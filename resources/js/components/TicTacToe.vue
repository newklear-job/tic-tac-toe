<template>
<div>
    <div class="left float-left online-users">
    <p>This users are online: </p>
    <ul>
        <li v-for="user in users">{{ user }} <button v-if="userId != user.id" class="btn btn-warning" @click="sendUser(user)">Send!</button> </li>
    </ul></div>


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
                                        <li v-for="item in messages">{{ item.message }} - <i>{{ item.user }}</i></li>
                                    </ul>

                                    <input type="text" v-model="message"><br/>
                                    <button class="btn btn-success" @click="send()">Send!</button>
                                </div>
                            </div>
                        </div>
                    </div>

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
                        console.log(this.chatMessage)
                        this.chatMessages.push({'message': this.chatMessage, 'user': 'Me'});
                        this.chatMessage = ''
                    });
            },
            sendUser(user){
                //console.log(user.id + ' - ' + user.name);
                axios.post('/userMessage', {message: 'go party!!!', toUser: user.id})
                    .then((response) => {
                    });
            },

            removeFromArray(array,  value){
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
                        .listen('.newMessage', (e) => {
                            console.log(e);
                        });

                });


        }
    }
</script>