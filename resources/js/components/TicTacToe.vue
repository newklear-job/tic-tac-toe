<template>
    <div>
        <div class="left float-left online-users">
            <p>This users are online: </p>
            <ul>
                <li v-for="user in users">{{ user }}
                    <button v-if="userId != user.id" class="btn btn-warning" @click="sendUserInvite(user)">Send invite!
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


                            <element-list :key="key" :mySide="mySide" :myTurn="myTurn"></element-list>


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
    import elementList from './elementList.vue';
    import {eventBus} from '../app';

    export default {
        components:{
            'element-list': elementList
        },
        data() {
            return {
                turns: {
                    true: [],
                    false: []
                },
                victoryArray: undefined,
                myTurn: undefined,
                mySide: undefined,

                gameStatus: "Here will be displayed your game status",
                roomId: undefined,
                playing: false,
                sentInvites: [],
                receivedInvites: [],
                users: [],
                userId: undefined,
                chatMessages: [],
                chatMessage: '',
                key: false
            }
        },

        methods: {
            sendTurn(turnData) {
                axios.post('/stepMessage', {message: turnData, 'room_id': this.roomId})
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
                            if (this.roomId !== undefined)
                                Echo.leave(`message.${this.roomId}`);
                            this.roomId = user.id + '-' + this.userId;
                            let enemyUser = this.users.filter(obj => {
                                return obj.id === user.id})[0];
                            this.gameStatus = 'Your game has beed started vs user ' + enemyUser.name + '. Your turn.';
                            acceptedInvite.status = "You are currently playing with this user";
                            this.playing = true;
                            this.myTurn=true;
                            this.mySide=true;
                            this.key = !this.key;
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
            terminateGame(){
                this.turns.true = [];
                this.turns.false = [];
                this.myTurn = undefined;
                this.mySide = undefined;
                // Echo.leave(`message.${this.roomId}`);
                //this.roomId = undefined;
                this.playing = false;
            },
            checkForVictory: function () {
                let playerX = this.loopCheck(this.turns.true);
                if (playerX) {
                    if (this.mySide === true)
                        this.gameStatus = 'You won!';
                    else
                        this.gameStatus = 'You lost!';
                    this.mySide = undefined;
                    this.terminateGame();
                    return playerX;
                }
                let playerY = this.loopCheck(this.turns.false);
                if (playerY) {
                    if (this.mySide === false)
                        this.gameStatus = 'You won!';
                    else
                        this.gameStatus = 'You lost!';
                    this.terminateGame();
                    return playerY;
                }
                if (this.turns.true.length + this.turns.false.length === 9) {
                    this.gameStatus = 'Draw';
                    this.terminateGame();
                    return true
                }
                return false
            },

            loopCheck: function (player) {
                for (let combination of this.victoryArray) {
                    var match = 0;
                    for (let cell of combination) {
                        if (!this.searchForArray(player, cell)) {
                            break;
                        } else {
                            match++;
                            if (match === 3)
                                return combination;
                        }
                    }
                }
                return false;
            },

            searchForArray: function (haystack, needle) {
                var i, j, current;
                for (i = 0; i < haystack.length; ++i) {
                    if (needle.length === haystack[i].length) {
                        current = haystack[i];
                        for (j = 0; j < needle.length && needle[j] === current[j]; ++j) ;
                        if (j === needle.length)
                            return true;
                    }
                }
                return false;
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
                        this.myTurn = true;
                        this.gameStatus = "Your turn!";
                        this.turns[!this.mySide].push(e.message);
                        eventBus.$emit('enemyStep', e.message);
                        let victoryCombination = this.checkForVictory();
                        if (victoryCombination.constructor === Array)
                        {
                            console.log(victoryCombination)
                        }
                    });
            }
        },
        created() {
            eventBus.$on('myStep', (data) =>{
                this.myTurn = false;
                this.gameStatus = "Enemies turn!";
                this.turns[this.mySide].push(data);
                this.sendTurn(data);
                let victoryCombination = this.checkForVictory();
                if (victoryCombination.constructor === Array)
                {
                    console.log(victoryCombination)
                }

            });
            this.victoryArray = [
                [[1, 1], [1, 2], [1, 3]],
                [[2, 1], [2, 2], [2, 3]],
                [[3, 1], [3, 2], [3, 3]],
                [[1, 1], [2, 1], [3, 1]],
                [[1, 2], [2, 2], [3, 2]],
                [[1, 3], [2, 3], [3, 3]],
                [[1, 1], [2, 2], [3, 3]],
                [[1, 3], [2, 2], [3, 1]]
            ];
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
                                if (this.roomId !== undefined)
                                    Echo.leave(`message.${this.roomId}`);
                                this.roomId = this.userId + '-' + response.user;
                                let enemyUser = this.users.filter(obj => {
                                    return obj.id === response.user})[0];
                                this.gameStatus = 'Your game has beed started vs user ' + enemyUser.name + '. His turn.';
                                this.playing = true;
                                this.myTurn = false;
                                this.mySide = false;
                                this.key = !this.key;
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
