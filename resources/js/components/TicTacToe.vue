<template>
    <div>
        <div class="left float-left online-users">
            <p>This users are online: </p>
            <ul>
                <li v-for="user in users">{{ user }}
                    <button v-if="userId != user.id" class="btn btn-warning" @click="sendUserInvite(user)">Send!</button>
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

                    <div v-show="recievedInvites.length">
                        This users sent you game invites:
                        <ul>
                            <li v-for="invite in recievedInvites">Username: {{invite.user.name}}, Date: {{
                                invite.timestamp | moment("HH:mm") }}
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
                roomId: '0-1',
                sentInvites: [],
                recievedInvites: [],
                users: [],
                userId: undefined,
                chatMessages: [],
                chatMessage: ''
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
                        // console.log(this.chatMessage)
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
                            'timestamp': Date.now()
                        });
                        console.log(this.sentInvites)
                    });
            },
            acceptUserInvite(user){
                let acceptedInvite = this.recievedInvites.filter(obj => {
                    return obj.user.id === user.id
                })[0];
                if (Date.now() - acceptedInvite.timestamp < 5*60000){  // 5 minutes
                    console.log('less than 1 minute')
                }
                else{
                    console.log('more than 1 minute')
                }
                // axios.post('/userMessage', {message: 'GO', toUser: user.id})
                //     .then((response) => {
                //         let sameUsersInvites = this.sentInvites.filter(obj => {
                //             return obj.user === user.id
                //         });
                //         if (sameUsersInvites.length)
                //             this.removeFromArray(this.sentInvites, sameUsersInvites[0]);
                //
                //         this.sentInvites.push({
                //             'user': user.id,
                //             'timestamp': Date.now()
                //         });
                //         console.log(this.sentInvites)
                //     });

            },

            removeFromArray(array, value) {
                let index = array.indexOf(value)
                array.splice(index, 1);
            }
        },

        mounted() {
            // Lobby chat listen
            Echo.join('lobby')
                .here((users) => {
                    this.users = users;
                })
                .joining((user) => {
                    // console.log(user)
                    this.users.push(user)
                })
                .leaving((user) => {
                    this.removeFromArray(this.users, user)
                })
                .listen('.newMessage', (e) => {
                    this.chatMessages.push(e)
                });

            //Private game chat listen
            Echo.join(`message.${this.roomId}`)
                .here((users) => {
                    // console.log(users);
                })
                .joining((user) => {

                    // console.log(`joined ${user.name}`);
                })
                .leaving((user) => {
                    // console.log(`leaved ${user.name}`);
                })
                .listen('.newMessage', (e) => {
                    this.messages.push(e)
                });

            // Get UserEvent Id + UserEvent chat listen
            axios.get('/getUserId')
                .then((response) => {
                    this.userId = response.data;
                    Echo.private(`user.${this.userId}`)
                        .listen('.newMessage', (response) => {
                            if (response.message === 'go party!!!') {
                                let sameUsersInvites = this.recievedInvites.filter(obj => {
                                    return obj.user.id === response.user
                                });
                                if (sameUsersInvites.length)
                                    this.removeFromArray(this.recievedInvites, sameUsersInvites[0]);
                                this.recievedInvites.push({
                                    'user': this.users.filter(obj => {
                                        return obj.id === response.user
                                    })[0],
                                    'timestamp': Date.now()
                                });
                                //console.log(this.recievedInvites[0].user)
                            }
                        });

                });


        }
    }
</script>