<template>
    <div>
        <div class="left float-left online-users">
            <p>This users are online: </p>
            <ul>
                <li v-for="user in users">{{ user }}
                    <button v-if="userId != user.id" class="btn btn-warning" @click="sendUserInvite(user)">Send!
                    </button>
                </li>
            </ul>
        </div>


        <div class="right float-right">
            <div class="panel panel-default">
                <div class="panel-heading">Example Chat</div>

                <div class="panel-body">
                    <ul>
                        <li v-for="item in chatMessages">{{ item.message }} - <i>{{ item.user }}</i></li>
                    </ul>

                    <input type="text" v-model="chatMessage" @keyup.enter='sendChat()'><br/>
                    <button class="btn btn-success" @click="sendChat()">Send!</button>
                </div>
            </div>
        </div>


        <div class="container">
            <div class="row">
                <div class="col-md-10 offset-md-1">
                    <div class="card">
                        <div class="card-header">Tic Tac Toe, User ID - {{userId}}</div>

                        <div class="card-body">
                            <h3>{{gameStatus}}</h3>
                            <div class="col-md-8 col-md-offset-2">
                                <div class="panel panel-default">
                                    <div class="panel-heading">Example Chat</div>

                                    <div class="panel-body">
                                        <ul>
                                            <li v-for="item in messages">{{ item.message }} - <i>{{ item.user }}</i>
                                            </li>
                                        </ul>

                                        <input type="text" v-model="message"><br/>
                                        <button class="btn btn-success" @click="send()">Send!</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>


                    <div v-show="receivedInvites.length">
                        This users sent you game invites:
                        <ul>
                            <li v-for="invite in receivedInvites">Username: {{invite.user.name}}, Date: {{
                                invite.date.getHours()}}:{{invite.date.getMinutes()}}, Status: {{invite.status}}
                                <button class="btn btn-warning" @click="acceptUserInvite(invite.user)">Accept invite
                                </button>
                            </li>
                        </ul>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>

<script>

    export default {
        data() {
            return {
                messages: [],
                message: '',
                gameStatus: "Here will be displayed your game status",
                roomId: undefined,
                playing: false,
                sentInvites: [],
                receivedInvites: [],
                users: [],
                userId: undefined,
                chatMessages: [],
                chatMessage: '',
            }
        },

        methods: {
            send() {
                axios.post('/message', {message: this.message, 'room_id': this.roomId})
                    .then((response) => {
                        this.message = '';
                    });
            },
            sendChat() {
                axios.post('/chatMessage', {message: this.chatMessage})
                    .then((response) => {
                        this.chatMessages.push({'message': this.chatMessage, 'user': 'Me'});
                        this.chatMessage = ''
                    });
            },
            sendUserInvite(user) {
                axios.post('/userMessage', {message: 'go party!!!', toUser: user.id})
                    .then((response) => {
                        let sameUsersInvites = this.sentInvites.filter(obj => {
                            return obj.user === user.id
                        });
                        if (sameUsersInvites.length)
                            this.removeFromArray(this.sentInvites, sameUsersInvites[0]);

                        this.sentInvites.push({
                            'user': user.id,
                            'date': new Date()
                        });
                    });
            },
            acceptUserInvite(user) {
                let acceptedInvite = this.receivedInvites.filter(obj => {
                    return obj.user.id === user.id
                })[0];
                if (this.playing) {
                    acceptedInvite.status = "You are already playing.";
                    return;
                }

                if (new Date() - acceptedInvite.date < 5 * 60000) {  // 5 minutes
                    axios.post('/userMessage', {message: 'GO NAH', toUser: user.id})
                        .then((response) => {
                            this.roomId = user.id + '-' + this.userId;
                            let enemyUser = this.users.filter(obj => {
                                return obj.id === user.id})[0];
                            this.gameStatus = 'Your game has beed started vs user ' + enemyUser.name;
                            acceptedInvite.status = "You are currently playing with this user";
                            this.playing = true;
                            this.startGame();

                        });
                } else {
                    acceptedInvite.status = "This invite has been expired";
                }

            },

            removeFromArray(array, value) {
                let index = array.indexOf(value)
                array.splice(index, 1);
            },
            startGame(){
                Echo.join(`message.${this.roomId}`)
                    .here((users) => {

                    })
                    .joining((user) => {

                    })
                    .leaving((user) => {
                        this.gameStatus = 'Your opponent left this lobby, you are disconnected too.'
                        Echo.leave(`message.${this.roomId}`);
                        this.playing = false;
                    })
                    .listen('.newMessage', (e) => {
                        this.messages.push(e)
                    });
            }
        },

        mounted() {
            // Lobby chat listen
            Echo.join('lobby')
                .here((users) => {
                    this.users = users;
                })
                .joining((user) => {
                    this.users.push(user)
                })
                .leaving((user) => {
                    this.removeFromArray(this.users, user)
                })
                .listen('.newMessage', (e) => {
                    this.chatMessages.push(e)
                });

            // Get UserEvent Id + UserEvent chat listen
            axios.get('/getUserId')
                .then((response) => {
                    this.userId = response.data;
                    Echo.private(`user.${this.userId}`)
                        .listen('.newMessage', (response) => {
                            if (response.message === 'go party!!!') {
                                let sameUsersInvites = this.receivedInvites.filter(obj => {
                                    return obj.user.id === response.user
                                });
                                if (sameUsersInvites.length)
                                    this.removeFromArray(this.receivedInvites, sameUsersInvites[0]);
                                this.receivedInvites.push({
                                    'user': this.users.filter(obj => {
                                        return obj.id === response.user
                                    })[0],
                                    'date': new Date(),
                                    'status': 'ok'
                                });
                            }
                            else if(response.message === 'GO NAH'){
                                let acceptedInvite = this.sentInvites.filter(obj => {
                                    return obj.user === response.user
                                });
                                if (!acceptedInvite.length) {
                                    axios.post('/userMessage', {
                                        message: 'i am not waiting for party with you',
                                        toUser: response.user
                                    })
                                        .then((response) => {
                                        });
                                    return;
                                }
                                this.roomId = this.userId + '-' + response.user;
                                let enemyUser = this.users.filter(obj => {
                                    return obj.id === response.user})[0];
                                this.gameStatus = 'Your game has beed started vs user ' + enemyUser.name;
                                this.playing = true;
                                this.startGame();

                            }

                            else if(response.message === "i am not waiting for party with you"){
                                this.gameStatus = 'Your opponent is not waiting for party with you! Please resend invite';
                                Echo.leave(`message.${this.roomId}`);
                                this.playing = false;
                                let maliciousInvite = this.receivedInvites.filter(obj => {
                                    return obj.user.id === response.user
                                })[0];
                                maliciousInvite.status = 'This invite is no longer valid!';
                            }

                        });

                });


        }
    }
</script>