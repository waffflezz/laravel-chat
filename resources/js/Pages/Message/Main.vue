<script>
import axios from "axios";
import Layout from "@/Pages/Layouts/Layout.vue";
import {Link} from "@inertiajs/vue3";
import * as PusherPushNotifications from "@pusher/push-notifications-web"

export default {
    name: "Index",
    components: {Layout, Link},
    props: [
        'currentUser',
        'otherUser',
        'users',
        'messages'
    ],
    data() {
        return {
            body: '',
            isSubscribed: false
        }
    },

    created() {
        const from_id = this.$page.props.auth.user.id;
        const to_id = this.otherUser.id;

        this.isSubscribe();

        Echo.private('store_messages.' + from_id + '.' + to_id)
            .listen('.store_message', res => {
                this.messages.push(res.message);
            })

    },

    methods: {
        store() {
            axios.post('/messages',
                {
                    body: this.body,
                    from_id: this.$page.props.auth.user.id,
                    to_id: this.otherUser.id,
                },
                {headers: {'X-Socket-ID': Echo.socketId()}})
                .then(res => {
                    this.messages.push(res.data);
                    this.body = '';
                    this.scrollToBottom();
                });
        },
        scrollToBottom() {
            const scrollContainer = this.$refs.scrollContainer;

            if (scrollContainer) {
                this.$nextTick(() => {
                    scrollContainer.scrollTop = scrollContainer.scrollHeight;
                });
            }
        },
        isSubscribe() {
            let script = document.createElement('script');
            script.src = "https://js.pusher.com/beams/1.0/push-notifications-cdn.js";
            script.async = true;
            document.body.appendChild(script);

            script.onload = () => {
                this.beamsClient = new PusherPushNotifications.Client({
                    instanceId: '20fa38ad-3a6f-4c32-a8f8-b06cbc89cd37',
                });

                this.beamsClient
                    .start()
                    .then(() => this.beamsClient.getDeviceInterests())
                    .then((intersects) => {
                        this.isSubscribed = intersects.includes(`user-${this.$page.props.auth.user.id}-${this.otherUser.id}`);
                    })
                    .catch(console.error);
            }
        },
        subscribe() {
            if (!this.isSubscribed) {
                this.beamsClient
                    .start()
                    .then(() => this.beamsClient.addDeviceInterest(`user-${this.$page.props.auth.user.id}-${this.otherUser.id}`))
                    .then(() => {
                        this.isSubscribed = true;
                    })
                    .catch(console.error);
            } else {
                this.beamsClient.removeDeviceInterest(`user-${this.$page.props.auth.user.id}-${this.otherUser.id}`)
                    .then(() => {
                        this.isSubscribed = false;
                    })
                    .catch(console.error);
            }
        }
    },

    mounted() {
        this.scrollToBottom();
    }
}
</script>

<template>
    <Layout>
        <div class="flex h-screen antialiased text-gray-800 w-full">
            <div class="flex flex-row h-full w-full overflow-x-hidden">
                <div class="flex flex-col py-8 pl-6 pr-2 w-64 bg-white flex-shrink-0">
                    <div class="flex flex-row items-center justify-center h-12">
                        <div
                            class="flex items-center justify-center rounded-2xl text-indigo-700 bg-indigo-100 h-10 w-10">
                            <svg
                                class="w-6 h-6"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                xmlns="http://www.w3.org/2000/svg">
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z">
                                </path>
                            </svg>
                        </div>
                        <div class="ml-2 font-bold text-2xl">QuickChat</div>
                    </div>
                    <div
                        class="flex flex-col items-center bg-indigo-100 border border-gray-200 mt-4 w-full py-6 px-4 rounded-lg">
                        <div class="h-20 w-20 rounded-full border overflow-hidden">
                            <img
                                src="https://avatars3.githubusercontent.com/u/2763884?s=128"
                                alt="Avatar"
                                class="h-full w-full"/>
                        </div>
                        <div class="text-sm font-semibold mt-2">{{ currentUser.name }}</div>
                        <div class="flex flex-row items-center mt-3">
                            <div class="flex flex-col justify-center h-4 w-8 bg-indigo-500 rounded-full">
                                <div class="h-3 w-3 bg-white rounded-full self-end mr-1"></div>
                            </div>
                            <div class="leading-none ml-1 text-xs">Active</div>
                        </div>
                    </div>

                    <button @click.prevent="subscribe" class="my-4 bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded-full shadow-md">
                        {{ isSubscribed ? 'Отписаться' : 'Подписаться' }}
                    </button>

                    <div class="flex flex-col mt-8">
                        <div class="flex flex-row items-center justify-between text-xs">
                            <span class="font-bold">Users</span>
                            <span class="flex items-center justify-center bg-gray-300 h-4 w-4 rounded-full">
                                {{ users.length }}
                            </span>
                        </div>
                        <div class="flex flex-col space-y-1 mt-4 -mx-2 overflow-y-auto">
                            <Link v-for="user in users"
                                  :href="route('index', {'otherUser': user.id})">
                                <button class="flex flex-row items-center hover:bg-gray-100 rounded-xl p-2">
                                    <div class="flex items-center justify-center h-8 w-8 bg-indigo-200 rounded-full">
                                        {{ user.name.charAt(0).toUpperCase() }}
                                    </div>
                                    <div class="ml-2 text-sm font-semibold">{{ user.name }}</div>
                                </button>
                            </Link>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col flex-auto h-full p-6 w-full">
                    <div class="flex flex-col flex-auto flex-shrink-0 rounded-2xl bg-gray-100 h-full p-4">
                        <div ref="scrollContainer" class="flex flex-col h-full overflow-x-auto mb-4">
                            <div class="flex flex-col h-full">
                                <div class="grid grid-cols-12 gap-y-2">
                                    <template v-for="message in messages">
                                        <div v-if="message.from_id !== $page.props.auth.user.id"
                                             class="col-start-1 col-end-8 p-3 rounded-lg">
                                            <div class="flex flex-row items-center">
                                                <div
                                                    class="flex items-center justify-center h-10 w-10 rounded-full bg-indigo-500 flex-shrink-0">
                                                    {{ otherUser.name.charAt(0).toUpperCase() }}
                                                </div>
                                                <div class="relative ml-3 text-sm bg-white py-2 px-4 shadow rounded-xl">
                                                    <div>{{ message.body }}</div>
                                                </div>
                                            </div>
                                        </div>


                                        <div v-else class="col-start-6 col-end-13 p-3 rounded-lg">
                                            <div class="flex items-center justify-start flex-row-reverse">
                                                <div
                                                    class="flex items-center justify-center h-10 w-10 rounded-full bg-indigo-500 flex-shrink-0">
                                                    {{ currentUser.name.charAt(0).toUpperCase() }}
                                                </div>
                                                <div
                                                    class="relative mr-3 text-sm bg-indigo-100 py-2 px-4 shadow rounded-xl">
                                                    <div>{{ message.body }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-row items-center h-16 rounded-xl bg-white w-full px-4">
                            <div>
                                <button class="flex items-center justify-center text-gray-400 hover:text-gray-600">
                                    <svg
                                        class="w-5 h-5"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"
                                        ></path>
                                    </svg>
                                </button>
                            </div>
                            <div class="flex-grow ml-4">
                                <div class="relative w-full">
                                    <input
                                        v-model="body"
                                        type="text"
                                        class="flex w-full border rounded-xl focus:outline-none focus:border-indigo-300 pl-4 h-10"
                                    />
                                    <button
                                        class="absolute flex items-center justify-center h-full w-12 right-0 top-0 text-gray-400 hover:text-gray-600">
                                        <svg
                                            class="w-6 h-6"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                            xmlns="http://www.w3.org/2000/svg">
                                            <path
                                                stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"
                                            ></path>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div class="ml-4">
                                <button
                                    @click.prevent="store"
                                    class="flex items-center justify-center bg-indigo-500 hover:bg-indigo-600 rounded-xl text-white px-4 py-1 flex-shrink-0">
                                    <span>Send</span>
                                    <span class="ml-2">
                  <svg
                      class="w-4 h-4 transform rotate-45 -mt-px"
                      fill="none"
                      stroke="currentColor"
                      viewBox="0 0 24 24"
                      xmlns="http://www.w3.org/2000/svg">
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        stroke-width="2"
                        d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"
                    ></path>
                  </svg>
                </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Layout>
</template>

<style scoped>

</style>
