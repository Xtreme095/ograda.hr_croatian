<?php defined('BASEPATH') || exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<?php
$csrfToken = $_SESSION['csrf_token'] ?? bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;
?>
<style>
    @media (max-width: 768px) {
        .grid {
            flex-direction: column;
            display: inherit;
        }

        aside {
            width: 100% !important;
            display: inherit;
        }

        main {
            width: 100% !important;
            display: inherit;
        }
    }

    .h-90vh {
        height: 90vh;
        overflow-y: scroll;
        overflow-x: hidden;
    }

    .overflow-y-auto {
        max-height: 90vh;
        overflow-y: scroll;
        overflow-x: hidden;
    }

    .no-gap {
        gap: 0;
    }

    .modern-background {}

    .modern-card {
        background-color: #fff;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        padding: 16px;
    }

    .modern-avatar {
        background-color: #3498db;
    }

    .modern-time {
        color: #7f8c8d;
    }

    .modern-unread {
        background-color: #e74c3c;
    }

    .modern-list-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }

    .header-icons i {
        margin-right: 15px;
        cursor: pointer;
        background-color: transparent; /* Remove background */
    }

    .header-icons i:hover {
        color: #3498db;
    }

    .emoji-picker {
        position: absolute;
        bottom: 60px;
        right: 20px;
        width: 300px;
        height: 300px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        overflow-y: scroll;
        overflow-x: hidden;
        display: none;
        padding: 10px;
        z-index: 10;
    }

    .emoji-picker.show {
        display: block;
    }

    .emoji-categories {
        display: flex;
        justify-content: space-around;
        margin-bottom: 10px;
    }

    .emoji-categories button {
        background: none;
        border: none;
        cursor: pointer;
        padding: 5px;
    }

    .emoji-categories button.active {
        border-bottom: 2px solid #3498db;
    }

    .emoji-picker ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .emoji-picker ul li {
        display: inline-block;
        padding: 5px;
        cursor: pointer;
        font-size: 20px;
    }

    .emoji-picker ul li:hover {
        background: #f0f4f8;
        border-radius: 4px;
    }

    .attachment-card {
        position: absolute;
        bottom: 80px;
        right: 20px;
        width: 200px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        overflow-x: hidden;
        display: none;
        padding: 10px;
        z-index: 10;
    }

    .attachment-card.show {
        display: block;
    }

    .attachment-card ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .attachment-card ul li {
        display: flex;
        align-items: center;
        padding: 10px;
        cursor: pointer;
        font-size: 16px;
    }

    .attachment-card ul li:hover {
        background: #f0f4f8;
        border-radius: 4px;
    }

    .attachment-card ul li i {
        margin-right: 10px;
    }

    .chat-background {
        background-image: url('assets/images/bg.jpg');
        background-size: cover;
        background-position: center;
    }

    .max-w-[300px] {
        max-width: 100%;
    }

    .max-h-[300px] {
        max-height: 100%;
    }

    .max-w-[60%] {
        max-width: 60%;
    }

    .bg-whatsapp-sent {
        background-color: #DCF8C6;
        box-shadow: rgba(149, 157, 165, 0.2) 0px 8px 24px;
    }

    .bg-whatsapp-received {
        background-color: #FFFFFF;
        box-shadow: rgba(149, 157, 165, 0.2) 0px 8px 24px;
    }

    .flex-row-reverse .bg-whatsapp-sent {
        margin-left: auto;
    }

    .flex-row-reverse .bg-whatsapp-received {
        margin-left: auto;
    }

  
    /* Icon Styling */
    .icon {
        color: #128C7E;
        font-size: 20px;
    }

    .icon:hover {
        color: #075E54;
    }

    /* Input Styling */
    input[type="text"] {
        border-radius: 20px;
        border: 1px solid #ddd;
        padding: 10px;
        width: 100%;
    }

    input[type="text"]:focus {
        border-color: #128C7E;
        box-shadow: 0 0 3px rgba(0, 0, 0, 0.1);
    }

    .avatar-wrapper {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #3498db;
        color: #fff;
        font-weight: bold;
        font-size: 1.2rem;
    }
</style>

<div id="wrapper" class="h-90vh flex flex-col font-sans">
    <div class="content">
        <div id="app">
            <section :class="isSidebarOpen ? 'grid grid-cols-1 md:grid-cols-4 no-gap' : 'grid grid-cols-1 md:grid-cols-5 no-gap'" class="h-90vh transition-all duration-300" style="box-shadow: rgba(100, 100, 111, 0.2) 0px 7px 29px 0px;">
                <!-- Collapsible Sidebar -->
                <aside :class="isSidebarOpen ? 'col-span-1 md:w-100' : 'w-0 col-span-0'" class="bg-gray-100 border-r border-gray-300 overflow-hidden flex flex-col transition-all duration-300">
                    <!-- Sidebar Content -->
                    <header class="bg-gray-200 flex items-center p-3 border-b border-gray-300" style="background: linear-gradient(360deg, #f0f4f8, #fff) !important;">
                        <div class="flex items-center justify-between w-full">
                            <button class="flex items-center bg-transparent border-none">
                                <img :src="whatsapp_selectedProfilePicture" alt="Profile Picture" class="h-10 w-10 rounded-full object-cover">
                                <div class="ml-3">
                                    <h1 class="font-bold text-lg text-gray-900">{{ whatsapp_selectedProfileName }}</h1>
                                </div>
                            </button>
                        </div>
                    </header>
                    <div v-if="isSidebarOpen" class="p-3" style="background: white !important;">
                        <div class="flex flex-wrap -mx-2 space-y-2 md:space-y-0">
                            <!-- Search Input -->
                            <div class="w-full md:w-1/3 lg:w-1/3 px-2">
                                <input id="whatsapp_searchText" v-model="whatsapp_searchText" type="text" 
                                       class="form-control w-full border border-gray-300 rounded-full p-2 bg-white 
                                              focus:outline-none focus:ring-2 focus:ring-blue-500"
                                       placeholder="Search or start new chat">
                            </div>
                    
                            <!-- WhatsApp Number Filter -->
                            <div class="w-full md:w-1/3 lg:w-1/3 px-2">
                                <select class="form-control w-full border border-gray-300 rounded-full p-2 bg-white 
                                              focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        v-model="whatsapp_selectedWaNo" @change="whatsapp_filterInteractions" id="whatsapp_selectedWaNo">
                                    <option v-for="(interaction, index) in whatsapp_uniqueWaNos" 
                                            :key="index" :value="interaction.phone_number_id">
                                        {{ interaction.phone_number }}
                                    </option>
                                    <option value="*">All Chats</option>
                                </select>
                            </div>
                    
                            <!-- Interaction Type Filter -->
                            <div class="w-full sm:w-1/3 lg:w-1/3 px-2">
                                <select class="form-control w-full border border-gray-300 rounded-full p-2 bg-white 
                                              focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        v-model="whatsapp_selectedInteractionType" @change="whatsapp_filterInteractions">
                                    <option value="*">All Types</option>
                                    <option v-for="type in whatsapp_uniqueInteractionTypes" :key="type" :value="type">
                                        {{ type }}
                                    </option>
                                </select>
                            </div>
                    
                            <!-- Status Filter -->
                            <div class="w-full sm:w-1/3 lg:w-1/3 px-2">
                                <select class="form-control w-full border border-gray-300 rounded-full p-2 bg-white 
                                              focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        v-model="whatsapp_selectedStatus" @change="whatsapp_filterInteractions">
                                    <option value="*">All Statuses</option>
                                    <option v-for="status in whatsapp_uniqueStatuses" :key="status.id" :value="status.id">
                                        {{ status.name }}
                                    </option>
                                </select>
                            </div>
                    
                            <!-- Assigned Staff Filter -->
                            <div class="w-full sm:w-1/3 lg:w-1/3 px-2">
                                <select class="form-control w-full border border-gray-300 rounded-full p-2 bg-white 
                                              focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        v-model="whatsapp_selectedAssigned" @change="whatsapp_filterInteractions">
                                    <option value="*">All Staff</option>
                                    <option v-for="staff in whatsapp_uniqueStaff" :key="staff.staffid" :value="staff.staffid">
                                        {{ staff.firstname }}
                                    </option>
                                </select>
                            </div>
                    
                            <!-- Unread/Active/Expired Filter -->
                            <div class="w-full sm:w-1/3 lg:w-1/3 px-2">
                                <select class="form-control w-full border border-gray-300 rounded-full p-2 bg-white 
                                              focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        v-model="whatsapp_selectedUnread" @change="whatsapp_filterInteractions">
                                    <option value="unread">Unread Chats</option>
                                    <option value="active">Active Chats</option>
                                    <option value="expired">Expired Chats</option>
                                </select>
                            </div>
                        </div>
                    </div>

                <section v-if="isSidebarOpen" class="overflow-y-auto flex-grow modern-background p-3">
                    <ul class="m-0 p-0 list-none">
                        <li v-for="chat in whatsapp_displayedInteractions" :key="chat.id" @click="whatsapp_selectinteraction(chat.id)" class="cursor-pointer transition-colors duration-200 modern-card modern-list-item">
                            <div class="flex items-center w-full">
                                <div class="h-12 w-12 rounded-full modern-avatar text-white flex items-center justify-center font-bold text-lg">
                                    {{ whatsapp_getAvatarInitials(chat.name) }}
                                </div>
                                <div class="ml-3 flex-grow">
                                    <header class="flex justify-between items-center w-full">
                                        <div class="flex items-center">
                                            <h6 class="font-bold text-gray-900">{{ chat.name }}</h6>
                                                <span v-if="chat.status === 'active'" class="ml-2 h-3 w-3 bg-green-500 rounded-full inline-block"></span>
                                                <span v-if="chat.status === 'expired'" class="ml-2 h-3 w-3 bg-red-500 rounded-full inline-block"></span>
                                        </div>
                                        <time class="text-sm modern-time ml-auto">{{ whatsapp_formatTime(chat.time_sent) }}</time>
                                    </header>
                                    <div class="flex justify-between items-center mt-1">
                                        <p class="text-gray-700 text-sm truncate" v-html="chat.last_message"></p>
                                        <ul class="flex gap-1 items-center">
                                            <li v-if="chat.unread !== '0'"><span class="modern-unread text-white rounded-full h-5 w-5 flex items-center justify-center">{{ chat.unread }}</span></li>
                                        </ul>
                                    </div>
                                    <div v-if="chat.type" class="flex items-center mt-1" style="font-size: 0.875rem;">
                                        <span v-if="chat.type === 'lead'">
                                            <a :href="chat.type_url" :onclick="'init_lead(' + chat.type_id + ');return false;'" style="background-color: #EDF2F7; color: #4A5568; border-radius: 0.25rem; padding: 0.25rem 0.5rem; font-size: 0.75rem;">
                                                Lead {{ chat.type_id }}
                                            </a>
                                            <span v-if="chat.lead_status_name" class="ml-2" :style="{'background-color': '#EDF2F7', 'color': '#4A5568', 'border-radius': '0.25rem', 'padding': '0.25rem 0.5rem', 'font-size': '0.75rem'}">
                                                {{ chat.lead_status_name }}
                                            </span>
                                        </span>
                                        <span v-if="chat.type" class="ml-2" :style="{'background-color': '#EDF2F7', 'color': '#4A5568', 'border-radius': '0.25rem', 'padding': '0.25rem 0.5rem', 'font-size': '0.75rem'}">{{ chat.type }}</span>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </ul>
                </section>

                </aside>
                <!-- Main Content Area -->
                <main :class="isSidebarOpen ? 'col-span-3' : 'col-span-5'" class="bg-gray-200 flex flex-col transition-all duration-300 chat-background h-90vh">
                    <header class="bg-gray-200 border-b border-gray-300 p-3 flex items-center justify-between" style="background: linear-gradient(360deg, #f0f4f8, #fff) !important;">
                        <div class="flex items-center">
                            <button @click="isSidebarOpen = !isSidebarOpen" class="icon is-only-mobile u-margin-end">
                                <span v-if="isSidebarOpen" class="icon icon-menu">☰</span>
                                <span v-else class="icon icon-menu">☰</span>
                            </button>
                            <div v-if="whatsapp_selectedinteraction" class="avatar-wrapper ml-3">
                                <div class="avatar">
                                    {{ whatsapp_getAvatarInitials(whatsapp_selectedinteraction.name || 'N/A') }}
                                </div>
                                <div class="ml-3">
                                    <h1 class="font-bold text-lg text-gray-900">{{ whatsapp_selectedinteraction.name }}</h1>
                                    <span class="text-sm text-gray-500" v-if="whatsapp_selectedinteraction.receiver_id">{{ whatsapp_selectedinteraction.receiver_id }}</span><br/>
                                    <span class="text-sm text-gray-500" v-if="whatsapp_selectedinteraction.last_msg_time">{{ whatsapp_alertTime(whatsapp_selectedinteraction.last_msg_time) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="header-icons flex items-center">
                            <i class="fas fa-paperclip" @click="toggleAttachmentCard"></i>
                        </div>
                    </header>

                    <div class="overflow-y-auto flex-grow p-4 h-90vh" :style="{ backgroundImage: 'url(' + whatsapp_chatbg + ')' }" ref="whatsapp_chatContainer">
                        <div v-if="whatsapp_selectedinteraction && whatsapp_selectedinteraction.messages">
                            <ol class="space-y-4">
                                <template v-for="(message, index) in whatsapp_selectedinteraction.messages" :key="index">
                                    <li v-if="whatsapp_shouldShowDate(message, whatsapp_selectedinteraction.messages[index - 1])" class="flex justify-center items-center my-4">
                                        <p class="inline-block p-2 bg-gray-200 text-gray-700 rounded-full text-xs shadow-md">{{ getDate(message.time_sent) }}</p>
                                    </li>
                                    <li :class="[
                                        'flex items-start mb-4 transition-transform transform duration-300 ease-in-out',
                                        { 'flex-row-reverse': message.nature === 'sent' }
                                    ]">
                                        <div :class="[
                                            'relative max-w-[60%] p-3 rounded-lg shadow-sm',
                                            message.nature === 'sent' ? 'bg-whatsapp-sent' : 'bg-whatsapp-received text-black'
                                        ]"
                                        v-bind="message.nature === 'sent' ? {
                                            'data-toggle': 'tooltip',
                                            'data-title': message.staff_name,
                                            'data-original-title': message.staff_name,
                                            'title': message.staff_name,
                                            'data-placement': 'left'
                                        } : {}" style="max-width: 60%; font-family: Helvetica, Arial, sans-serif;">

                                            <!-- Show replies -->
                                            <template v-if="message.ref_message_id">
                                                <div class="bg-gray-100 p-2 mt-2 rounded-lg border border-gray-300 shadow-inner">
                                                    <p class="text-xs text-gray-600">Replying to:</p>
                                                    <p class="text-sm font-medium" v-html="getOriginalMessage(message.ref_message_id).message">{{ getOriginalMessage(message.ref_message_id).message }}</p>
                                                </div>
                                                <p class="text-sm font-normal" v-html="message.message" style="font-size: 15px; line-height: 1.4;">{{ message.message }}</p>
                                            </template>
                                            
                                            <!-- Text Message -->
                                            <template v-else-if="message.type === 'text'">
                                                <p class="text-sm" v-html="message.message">{{ message.message }}</p>
                                            </template>

                                            <!-- Image Message -->
                                            <template v-else-if="message.type === 'image'">
                                                <a :href="message.asset_url" data-lightbox="image-group">
                                                    <img :src="message.asset_url" alt="Image" class="rounded-lg shadow-md max-w-[300px] max-h-[300px]" style="border-radius: 10px;">
                                                </a>
                                                <p class="text-sm mt-2" v-if="message.caption" style="font-size: 15px; line-height: 1.4;">{{ message.caption }}</p>
                                            </template>

                                            <!-- Video Message -->
                                            <template v-else-if="message.type === 'video'">
                                                <video :src="message.asset_url" controls class="rounded-lg shadow-md max-w-[300px] max-h-[300px]" style="border-radius: 10px;"></video>
                                                <p class="text-sm mt-2" v-if="message.message" style="font-size: 15px; line-height: 1.4;">{{ message.message }}</p>
                                            </template>

                                            <!-- Document Message -->
                                            <template v-else-if="message.type === 'document'">
                                                <a :href="message.asset_url" target="_blank" class="text-blue-500 hover:text-blue-700 underline" style="font-size: 15px; line-height: 1.4;">Download Document</a>
                                            </template>

                                            <!-- Audio Message -->
                                            <template v-else-if="message.type === 'audio'">
                                                <audio controls class="max-w-[300px]">
                                                    <source :src="message.asset_url" type="audio/mpeg">
                                                </audio>
                                            </template>

                                            <!-- Carousel Message -->
                                            <template v-else-if="message.type === 'carousel'">
                                                <div class="carousel">
                                                    <div v-for="(item, idx) in message.carousel_items" :key="idx" class="carousel-item rounded-lg shadow-md p-2">
                                                        <img :src="item.image_url" alt="Carousel Image" class="rounded-lg max-w-[300px] max-h-[300px]">
                                                        <p class="text-sm mt-2 font-semibold" style="font-size: 15px; line-height: 1.4;">{{ item.title }}</p>
                                                        <p class="text-sm mt-1" style="font-size: 15px; line-height: 1.4;">{{ item.subtitle }}</p>
                                                        <a :href="item.button_url" target="_blank" class="text-blue-500 hover:text-blue-700 underline">{{ item.button_text }}</a>
                                                    </div>
                                                </div>
                                            </template>

                                            <!-- Sticker Message -->
                                            <template v-else-if="message.type === 'sticker'">
                                                <img :src="message.asset_url" alt="Sticker" class="rounded-lg max-w-[300px] max-h-[300px] shadow-md">
                                            </template>

                                            <!-- Location Message -->
                                            <template v-else-if="message.type === 'location'">
                                                <p class="text-sm font-semibold" style="font-size: 15px; line-height: 1.4;">Location: {{ message.location_name }}</p>
                                                <p class="text-sm" style="font-size: 15px; line-height: 1.4;">Address: {{ message.location_address }}</p>
                                            </template>

                                            <!-- Contact Message -->
                                            <template v-else-if="message.type === 'contacts'">
                                                <div v-for="(contact, idx) in message.contacts" :key="idx" class="p-2 border rounded-lg bg-gray-100">
                                                    <p class="text-sm font-bold" style="font-size: 15px; line-height: 1.4;">{{ contact.name.formatted_name }}</p>
                                                    <p class="text-sm" style="font-size: 15px; line-height: 1.4;">{{ contact.phones[0].phone }}</p>
                                                    <p class="text-sm" v-if="contact.emails[0].email" style="font-size: 15px; line-height: 1.4;">{{ contact.emails[0].email }}</p>
                                                </div>
                                            </template>

                                            <!-- Poll Message -->
                                            <template v-else-if="message.type === 'poll'">
                                                <p class="text-sm font-bold" style="font-size: 15px; line-height: 1.4;">{{ message.poll_question }}</p>
                                                <ul class="mt-2 space-y-1">
                                                    <li v-for="(option, idx) in message.poll_options" :key="idx" class="text-sm" style="font-size: 15px; line-height: 1.4;">
                                                        <span class="font-semibold">{{ option }}:</span> {{ message.poll_results[idx] }} votes
                                                    </li>
                                                </ul>
                                            </template>

                                            <!-- Template Message -->
                                            <template v-else-if="message.type === 'template'">
                                                <p class="text-sm font-normal" v-html="message.message" style="font-size: 15px; line-height: 1.4;">{{ message.message }}</p>
                                            </template>

                                            <!-- Welcome Request Message -->
                                            <template v-else-if="message.type === 'request_welcome'">
                                                <div class="bg-blue-100 p-3 rounded-lg border border-blue-300 shadow-inner">
                                                    <p class="text-sm text-blue-700" style="font-size: 15px; line-height: 1.4;">{{ message.message }}</p>
                                                </div>
                                            </template>

                                            <!-- Message Status and Actions -->
                                            <div class="flex justify-between items-center mt-2 text-xs text-gray-500">
                                                <span>{{ whatsapp_getTime(message.time_sent) }}</span>
                                                
                                                <span v-if="message.nature === 'sent'" class="ml-2 flex items-center">
                                                    <!-- Display status icons based on message status -->
                                                    <i v-if="message.status === 'sent'" class="fa fa-check" title="Sent"></i>
                                                    <i v-else-if="message.status === 'delivered'" class="fa fa-check-double" title="Delivered"></i>
                                                    <i v-else-if="message.status === 'read'" class="fa fa-check-double text-blue-500" title="Read"></i>
                                                    <!-- New status icons -->
                                                    <i v-else-if="message.status === 'failed'" class="fa fa-times text-red-500" title="Failed"></i>
                                                    <i v-else-if="message.status === 'accepted'" class="fa fa-check-circle text-green-500" title="Accepted"></i>
                                                </span>
                                                
                                                <span class="ml-auto flex items-center">
                                                    <i class="fas fa-reply cursor-pointer" @click="replyToMessage(message)" title="Reply"></i>
                                                </span>
                                            </div>

                                        </div>
                                    </li>
                                </template>
                            </ol>
                        </div>
                    </div>
                    <!-- Message Form -->
                    <form v-on:submit.prevent="whatsapp_sendMessage" class="bg-gray-200 p-5 border-t border-gray-300 relative flex flex-col" style="background: linear-gradient(360deg, rgb(240, 244, 248), rgb(255, 255, 255)) !important;">
                        <div v-if="replyingToMessage" class="flex items-center mb-2 bg-gray-100 p-2 rounded-lg">
                            <div class="flex-grow">
                                <p class="text-xs text-gray-600">Replying to:</p>
                                <p class="text-sm">{{ replyingToMessage.message }}</p>
                            </div>
                            <button @click="clearReply" class="text-xs text-red-500 ml-2">Cancel</button>
                        </div>
                        <ul v-if="showQuickReplies" class="flex-grow bg-white shadow-md rounded-lg mt-2 p-2">
                            <li v-for="(reply, index) in filteredQuickReplies"
                                :key="index"
                                @click="selectQuickReply(index)"
                                :class="{
                                    'bg-blue-100 text-blue-900': index === quickReplyIndex,
                                    'hover:bg-gray-100 cursor-pointer rounded-md p-2 transition-all duration-200 ease-in-out': true
                                }">
                                {{ reply.message }}
                            </li>
                        </ul>

                        <div class="flex items-center w-full">
                            <span class="relative">
                                <i class="fa fa-smile cursor-pointer mx-2 text-gray-500 hover:text-gray-700" @click="toggleEmojiPicker"></i>
                                <!-- Emoji Picker -->
                                <div class="emoji-picker absolute bottom-12 left-0" :class="{ 'show': showEmojiPicker }">
                                    <div class="emoji-categories">
                                        <button v-for="category in emojiCategories" @click="selectedCategory = category" :class="{ 'active': selectedCategory === category }">
                                            {{ category }}
                                        </button>
                                    </div>
                                    <ul>
                                        <li v-for="emoji in emojis[selectedCategory]" @click="addEmoji(emoji)">{{ emoji }}</li>
                                    </ul>
                                </div>
                            </span>
                            <input type="text" v-model="whatsapp_newMessage" @input="handleInput" @keydown.down.prevent="navigateQuickReplies(1)" @keydown.up.prevent="navigateQuickReplies(-1)" class="form-control flex-grow border-none p-2 rounded-full mx-2" placeholder="Type your message..." id="whatsapp_newMessage" style="border: none; padding: 10px; margin: 0 10px;">
                            <span class="flex items-center relative">
                                <i class="fa fa-paperclip cursor-pointer mx-2 text-gray-500 hover:text-gray-700" @click="toggleAttachmentCard"></i>
                                <!-- Attachment Card -->
                                <div class="attachment-card absolute bottom-12 right-0" :class="{ 'show': showAttachmentCard }">
                                    <ul>
                                        <li @click="triggerFileInput('image')">
                                            <i class="fa fa-image text-blue-500"></i> Image
                                        </li>
                                        <li @click="triggerFileInput('video')">
                                            <i class="fa fa-video text-green-500"></i> Video
                                        </li>
                                        <li @click="triggerFileInput('document')">
                                            <i class="fa fa-file text-orange-500"></i> Document
                                        </li>
                                    </ul>
                                </div>
                                <input type="file" ref="imageAttachmentInput" style="display: none;" @change="whatsapp_handleImageAttachmentChange">
                                <input type="file" ref="videoAttachmentInput" style="display: none;" @change="whatsapp_handleVideoAttachmentChange">
                                <input type="file" ref="documentAttachmentInput" style="display: none;" @change="whatsapp_handleDocumentAttachmentChange">
                                <button v-on:click="whatsapp_toggleRecording" type="button" class="btn btn-default mx-1">
                                    <span v-if="!whatsapp_recording" class="fa fa-microphone text-gray-500 hover:text-gray-700" data-toggle="tooltip" data-title="Record Audio" data-placement="top" title=""></span>
                                    <span v-else class="fas fa-stop rounded-full p-2 text-red-500 hover:text-red-700"></span>
                                </button>
                                <button v-if="whatsapp_showSendButton || whatsapp_audioBlob" type="submit" class="btn btn-default mx-1">
                                    <i class="fa fa-paper-plane text-gray-500 hover:text-gray-700"></i>
                                </button>
                            </span>
                        </div>
                    </form>
                </main>
            </section>
        </div>
    </div>
</div>




<?php init_tail(); ?>
<script src="<?php echo module_dir_url(WHATSAPP_MODULE, 'assets/js/chat.js'); ?>"></script>
<script src="<?php echo module_dir_url(WHATSAPP_MODULE, 'assets/js/vue.min.js'); ?>"></script>
<script src="<?php echo module_dir_url(WHATSAPP_MODULE, 'assets/js/axios.min.js'); ?>"></script>
<script src="<?php echo module_dir_url(WHATSAPP_MODULE, 'assets/js/recorder-core.js'); ?>"></script>
<script src="<?php echo module_dir_url(WHATSAPP_MODULE, 'assets/js/purify.min.js'); ?>"></script>
<script src="<?php echo module_dir_url(WHATSAPP_MODULE, 'assets/js/mp3-engine.js'); ?>"></script>
<script src="<?php echo module_dir_url(WHATSAPP_MODULE, 'assets/js/mp3.js'); ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script>
"use strict";
new Vue({
    el: '#app',
    data() {
        return {
            interactions: [],
            whatsapp_selectedinteractionIndex: null,
            whatsapp_selectedinteraction: null,
            whatsapp_selectedinteractionMobNo: null,
            whatsapp_selectedinteractionSenderNo: null,
            whatsapp_newMessage: '',
            whatsapp_imageAttachment: null,
            whatsapp_videoAttachment: null,
            whatsapp_documentAttachment: null,
            whatsapp_imagePreview: '',
            whatsapp_videoPreview: '',
            whatsapp_csrfToken: '<?php echo $csrfToken; ?>',
            whatsapp_recording: false,
            whatsapp_audioBlob: null,
            whatsapp_recordedAudio: null,
            errorMessage: '',
            whatsapp_searchText: '',
            whatsapp_selectedWaNo: '*',
            whatsapp_selectedInteractionType: '*',
            whatsapp_selectedStatus: '*',
            whatsapp_selectedAssigned: '*',
            whatsapp_selectedUnread: 'active', // New property for unread filter
            whatsapp_filteredInteractions: [],
            whatsapp_displayedInteractions: [],
            whatsapp_profilePictureUrl: null,
            whatsapp_uniqueWaNos: <?php echo json_encode($numbers); ?>,
            whatsapp_uniqueInteractionTypes: ["staff", "lead", "contact", "customer"],
            whatsapp_uniqueStatuses: <?php echo json_encode($statuses); ?>,
            whatsapp_uniqueStaff: <?php echo json_encode($staffs); ?>,
            whatsapp_quickreplies: <?php echo json_encode($quick_replies); ?>,
            isSidebarOpen: true,
            whatsapp_selectedProfilePicture: '<?php echo module_dir_url(WHATSAPP_MODULE, 'assets/images/icon.png'); ?>',
            whatsapp_chatbg: '<?php echo module_dir_url(WHATSAPP_MODULE, 'assets/images/bg.jpg'); ?>',
            whatsapp_selectedProfileName: '',
            showQuickReplies: false,
            filteredQuickReplies: [],
            showEmojiPicker: false,
            quickReplyIndex: -1,
            showAttachmentCard: false,
            emojis: null,
            emojiCategories: [],
            selectedCategory: 'Smileys',
            replyingToMessage: null,
        };
    },
    methods: {
        async whatsapp_selectinteraction(id) {
            try {
                // Make the AJAX request to mark the chat as read
                $.ajax({
                    url: `${admin_url}whatsapp/chat_mark_as_read`,
                    type: 'POST',
                    dataType: 'html',
                    data: {
                        'interaction_id': id
                    },
                });
        
                // Find the interaction directly by its id
                const selectedInteraction = this.interactions.find(interaction => interaction.id === id);
        
                if (selectedInteraction) {
                    // Set the selected interaction properties
                    this.whatsapp_selectedinteraction = selectedInteraction;
                    this.whatsapp_selectedinteractionMobNo = selectedInteraction['receiver_id'];
                    this.whatsapp_selectedinteractionSenderNo = selectedInteraction['wa_no'];
                    this.whatsapp_scrollToBottom();
                } else {
                    console.error('Interaction not found.');
                }
        
            } catch (error) {
                console.error('Error marking chat as read:', error);
            }
        },
        async whatsapp_sendMessage() {
            if (!this.whatsapp_selectedinteraction || !this.whatsapp_selectedinteraction.id || !this.whatsapp_selectedinteraction.receiver_id) {
                console.error('Selected interaction is not properly initialized.');
                return;
            }

            const whatsapp_formData = new FormData();
            whatsapp_formData.append('id', this.whatsapp_selectedinteraction.id);
            whatsapp_formData.append('to', this.whatsapp_selectedinteraction.receiver_id);
            whatsapp_formData.append('csrf_token_name', this.whatsapp_csrfToken);

            const MAX_MESSAGE_LENGTH = 2000;
            if (this.whatsapp_newMessage.length > MAX_MESSAGE_LENGTH) {
                this.whatsapp_newMessage = this.whatsapp_newMessage.substring(0, MAX_MESSAGE_LENGTH);
            }
            if (this.whatsapp_newMessage.trim()) {
                whatsapp_formData.append('message', DOMPurify.sanitize(this.whatsapp_newMessage));
            }

            if (this.whatsapp_imageAttachment) {
                whatsapp_formData.append('image', this.whatsapp_imageAttachment);
            }

            if (this.whatsapp_videoAttachment) {
                whatsapp_formData.append('video', this.whatsapp_videoAttachment);
            }

            if (this.whatsapp_documentAttachment) {
                whatsapp_formData.append('document', this.whatsapp_documentAttachment);
            }

            if (this.whatsapp_audioBlob) {
                whatsapp_formData.append('audio', this.whatsapp_audioBlob, 'audio.mp3');
            }

            if (this.replyingToMessage) {
                whatsapp_formData.append('ref_message_id', this.replyingToMessage.message_id);
            }

            try {
                await axios.post('<?php echo admin_url('whatsapp/webhook/send_message'); ?>', whatsapp_formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                });

                this.whatsapp_clearAttachments();
                this.whatsapp_filterInteractions();
                this.whatsapp_selectinteraction(this.whatsapp_selectedinteraction.id);
                this.errorMessage = '';
                this.whatsapp_scrollToBottom();
                this.replyingToMessage = null;
            } catch (error) {
                this.handleErrorResponse(error);
            }
        },
        whatsapp_clearMessage() {
            this.whatsapp_newMessage = '';
            this.whatsapp_clearAttachments();
        },
        whatsapp_handleAttachmentChange(event) {
            const files = event.target.files;
            this.attachment = files[0];
        },
        async whatsapp_fetchinteractions() {
            try {
                const whatsapp_response = await axios.get('<?php echo admin_url('whatsapp/interactions'); ?>', {
                    params: {
                        wa_no_id: this.whatsapp_selectedWaNo,
                        interaction_type: this.whatsapp_selectedInteractionType,
                        status_id: this.whatsapp_selectedStatus,
                        assigned_staff_id: this.whatsapp_selectedAssigned,
                        status: this.whatsapp_selectedUnread
                    }
                });
                const data = await whatsapp_response.data;
                this.interactions = data.interactions || [];
                this.whatsapp_filterInteractions();
                this.whatsapp_updateSelectedInteraction();
            } catch (error) {
                console.error('Error fetching interactions:', error);
            }
        },
        whatsapp_updateSelectedInteraction() {
            if (Array.isArray(this.interactions)) { // Added check
                const whatsapp_new_index = this.interactions.findIndex(interaction => 
                    interaction.receiver_id === this.whatsapp_selectedinteractionMobNo && 
                    interaction.wa_no === this.whatsapp_selectedinteractionSenderNo
                );
                if (whatsapp_new_index !== -1) {
                    this.whatsapp_selectedinteraction = this.interactions[whatsapp_new_index];
                }
            }
        },
        whatsapp_getTime(timeString) {
            return timeString ? timeString.split(' ')[1] : '';
        },
        getDate(dateString) {
            const whatsapp_date = new Date(dateString);
            return whatsapp_date.toLocaleDateString('en-GB', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            }).replace(/ /g, '-');
        },
        whatsapp_shouldShowDate(currentMessage, previousMessage) {
            if (!previousMessage) return true;
            return this.getDate(currentMessage.time_sent) !== this.getDate(previousMessage.time_sent);
        },
        whatsapp_scrollToBottom() {
            this.$nextTick(() => {
                const whatsapp_chatContainer = this.$refs.whatsapp_chatContainer;
                if (whatsapp_chatContainer) {
                    whatsapp_chatContainer.scrollTop = whatsapp_chatContainer.scrollHeight;
                }
            });
        },
        whatsapp_getAvatarInitials(name) {
            return name.split(' ').slice(0, 2).map(word => word.charAt(0)).join('').toUpperCase();
        },
        whatsapp_countUnreadMessages(interactionId) {
            const interaction = this.interactions.find(inter => inter.id === interactionId);
            return interaction ? interaction.messages.filter(message => message.is_read == 0).length : 0;
        },
        async whatsapp_toggleRecording() {
            if (!this.whatsapp_recording) {
                this.whatsapp_startRecording();
            } else {
                this.whatsapp_stopRecording();
            }
        },
        toggleEmojiPicker() {
            this.showEmojiPicker = !this.showEmojiPicker;
        },
        addEmoji(emoji) {
            this.whatsapp_newMessage += emoji;
        },
        toggleAttachmentCard() {
            this.showAttachmentCard = !this.showAttachmentCard;
        },
        triggerFileInput(type) {
            const inputRef = this.$refs[`${type}AttachmentInput`];
            if (inputRef) {
                inputRef.click();
                this.showAttachmentCard = false;
            } else {
                console.error(`Element with ref ${type}AttachmentInput not found`);
            }
        },
        whatsapp_startRecording() {
            if (!this.recorder) {
                this.recorder = new Recorder({
                    type: "mp3",
                    sampleRate: 16000,
                    bitRate: 16
                });
            }
            this.recorder.open(() => {
                this.whatsapp_recording = true;
                this.recorder.start();
            }, err => {
                console.error("Failed to start recording:", err);
            });
        },
        whatsapp_stopRecording() {
            if (this.recorder && this.whatsapp_recording) {
                this.recorder.stop((blob) => {
                    this.recorder.close();
                    this.whatsapp_recording = false;
                    this.whatsapp_audioBlob = blob;
                    this.whatsapp_sendMessage();
                    this.whatsapp_recordedAudio = URL.createObjectURL(blob);
                }, err => {
                    console.error("Failed to stop recording:", err);
                });
            }
        },
        whatsapp_handleImageAttachmentChange(event) {
            this.whatsapp_imageAttachment = event.target.files[0];
            this.whatsapp_imagePreview = URL.createObjectURL(this.whatsapp_imageAttachment);
        },
        whatsapp_handleVideoAttachmentChange(event) {
            this.whatsapp_videoAttachment = event.target.files[0];
            this.whatsapp_videoPreview = URL.createObjectURL(this.whatsapp_videoAttachment);
        },
        whatsapp_handleDocumentAttachmentChange(event) {
            this.whatsapp_documentAttachment = event.target.files[0];
        },
        whatsapp_removeImageAttachment() {
            this.whatsapp_imageAttachment = null;
            this.whatsapp_imagePreview = '';
        },
        whatsapp_removeVideoAttachment() {
            this.whatsapp_videoAttachment = null;
            this.whatsapp_videoPreview = '';
        },
        whatsapp_removeDocumentAttachment() {
            this.whatsapp_documentAttachment = null;
        },
        whatsapp_formatTime(timestamp) {
            const currentDate = new Date();
            const messageDate = new Date(timestamp);
            const diffInHours = (currentDate - messageDate) / (1000 * 60 * 60);

            if (diffInHours < 24) {
                const hour = messageDate.getHours();
                const minute = messageDate.getMinutes();
                const period = hour < 12 ? 'AM' : 'PM';
                return `${hour % 12 || 12}:${minute < 10 ? '0' + minute : minute} ${period}`;
            } else {
                return `${messageDate.getDate()}-${messageDate.getMonth() + 1}-${messageDate.getFullYear() % 100}`;
            }
        },
        whatsapp_alertTime(lastMsgTime) {
            if (!lastMsgTime) return '';

            const currentDate = new Date();
            const messageDate = new Date(lastMsgTime);
            const diffInMs = currentDate - messageDate;
            const diffInHours = Math.floor(diffInMs / (1000 * 60 * 60));
            const diffInMinutes = Math.floor((diffInMs % (1000 * 60 * 60)) / (1000 * 60));

            if (diffInHours < 24) {
                const remainingHours = 23 - diffInHours;
                const remainingMinutes = 60 - diffInMinutes;
                return `Reply within ${remainingHours} hours and ${remainingMinutes} minutes`;
            }
            return '';
        },
        whatsapp_stripHTMLTags(str) {
            return str.replace(/<\/?[^>]+(>|$)/g, "");
        },
        whatsapp_truncateText(text, wordLimit) {
            const strippedText = this.whatsapp_stripHTMLTags(text);
            const whatsapp_words = strippedText.split(' ');
            return whatsapp_words.length > wordLimit ? `${whatsapp_words.slice(0, wordLimit).join(' ')}...` : text;
        },
        whatsapp_filterInteractions() {
            let filtered = this.interactions;

            this.whatsapp_filteredInteractions = filtered;
            this.whatsapp_searchInteractions();
        },
        whatsapp_searchInteractions() {
            if (this.whatsapp_searchText) {
                this.whatsapp_displayedInteractions = this.whatsapp_filteredInteractions.filter(interaction =>
                    interaction.name.toLowerCase().includes(this.whatsapp_searchText.toLowerCase())
                );
            } else {
                this.whatsapp_displayedInteractions = this.whatsapp_filteredInteractions;
            }
        },
        whatsapp_markInteractionAsRead(interactionId) {
            const interaction = this.interactions.find(interaction => interaction.id === interactionId);
            if (interaction) {
                interaction.read = true;
            }

            fetch('<?php echo admin_url('whatsapp/webhook/mark_interaction_as_read'); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        interaction_id: interactionId,
                        csrf_token_name: this.whatsapp_csrfToken
                    }),
                })
                .then(whatsapp_response => {
                    if (!whatsapp_response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return whatsapp_response.json();
                })
                .catch(error => {
                    console.error('Error marking interaction as read:', error);
                    if (interaction) {
                        interaction.read = false;
                    }
                });
        },
        handleErrorResponse(error) {
            const whatsapp_rawErrorMessage = error.response && error.response.data ? error.response.data : 'An error occurred. Please try again.';
            const whatsapp_typeRegex = /<p>Type: (.+)<\/p>/;
            const whatsapp_messageRegex = /<p>Message: (.+)<\/p>/;
            const whatsapp_typeMatch = whatsapp_rawErrorMessage.match(whatsapp_typeRegex);
            var whatsapp_messageMatch = whatsapp_rawErrorMessage.match(whatsapp_messageRegex);

            if (typeof(whatsapp_messageMatch[1] == 'object')) {
                whatsapp_messageMatch[1] = JSON.parse(whatsapp_messageMatch[1]);
                whatsapp_messageMatch[1] = whatsapp_messageMatch[1].error.message;
            }

            const whatsapp_getTypeText = whatsapp_typeMatch ? whatsapp_typeMatch[1] : '';
            const whatsapp_getMessageText = whatsapp_messageMatch ? whatsapp_messageMatch[1] : '';

            const errorMessage = whatsapp_getTypeText.trim() + '\n' + whatsapp_getMessageText.trim();
            this.errorMessage = errorMessage;
        },
        whatsapp_clearAttachments() {
            this.whatsapp_newMessage = '';
            this.whatsapp_imageAttachment = null;
            this.whatsapp_videoAttachment = null;
            this.whatsapp_documentAttachment = null;
            this.whatsapp_audioBlob = null;
            this.whatsapp_imagePreview = '';
            this.whatsapp_videoPreview = '';
        },
        updateProfileData() {
            const selectedInteraction = this.whatsapp_uniqueWaNos.find(interaction => interaction.phone_number_id === this.whatsapp_selectedWaNo);
            if (selectedInteraction) {
                this.whatsapp_selectedProfilePicture = selectedInteraction.profile_picture_url || '<?php echo module_dir_url(WHATSAPP_MODULE, 'assets/images/icon.png'); ?>';
                this.whatsapp_selectedProfileName = selectedInteraction.phone_number;
            } else {
                this.whatsapp_selectedProfilePicture = '<?php echo module_dir_url(WHATSAPP_MODULE, 'assets/images/icon.png'); ?>';
                this.whatsapp_selectedProfileName = 'All Chats';
            }
        },
        handleInput() {
            if (this.whatsapp_newMessage.endsWith('/')) {
                this.showQuickReplies = true;
                this.filteredQuickReplies = this.whatsapp_quickreplies.filter(reply => reply.message.toLowerCase().includes(this.whatsapp_newMessage.toLowerCase().slice(0, -1)));
                this.quickReplyIndex = -1;
            } else {
                this.showQuickReplies = false;
            }
        },
        navigateQuickReplies(direction) {
            if (!this.showQuickReplies) return;
            const totalReplies = this.filteredQuickReplies.length;
            this.quickReplyIndex = (this.quickReplyIndex + direction + totalReplies) % totalReplies;
        },
        selectQuickReply(index = this.quickReplyIndex) {
            if (index >= 0 && index < this.filteredQuickReplies.length) {
                const selectedReply = this.filteredQuickReplies[index].message;
                this.whatsapp_newMessage = selectedReply;
                this.showQuickReplies = false;
            }
        },
       async loadEmojis() {
            try {
                const response = await fetch('<?php echo module_dir_url(WHATSAPP_MODULE, 'assets/json/emojis.json'); ?>');
                const data = await response.json();
                if (data && typeof data === 'object') { // Added check
                    this.emojiCategories = Object.keys(data);
                    this.emojis = data;
                    this.selectedCategory = this.emojiCategories[0];
                } else {
                    throw new Error('Invalid emoji data');
                }
            } catch (error) {
                console.error('Error loading emojis:', error);
            }
        },
        selectCategory(category) {
            this.selectedCategory = category;
        },
        getOriginalMessage(refMessageId) {
            return this.whatsapp_selectedinteraction.messages.find(msg => msg.message_id === refMessageId) || {};
        },
        replyToMessage(message) {
            this.replyingToMessage = message;
        },
        clearReply() {
            this.replyingToMessage = null;
        },
        openLeadModal(leadId) {
            const leadUrl = `${admin_url('leads/index/')}${leadId}`;
            window.open(leadUrl, '_blank');
        },
        handleWelcomeRequest(message) {
            this.whatsapp_newMessage = "Welcome! How can we assist you today?";
        }
    },
    watch: {
        whatsapp_selectedWaNo(newVal, oldVal) {
            this.updateProfileData();
            this.whatsapp_filterInteractions();
        }
    },
    created() {
        this.whatsapp_fetchinteractions().then(() => {
                setInterval(() => {
                    this.whatsapp_fetchinteractions();
                }, 5000);
            this.updateProfileData();
            this.loadEmojis();
        });
    },
    computed: {
        whatsapp_selectedInteraction() {
            return this.whatsapp_selectedinteractionIndex !== null ? this.interactions[this.whatsapp_selectedinteractionIndex] : null;
        },
        whatsapp_showSendButton() {
            return this.whatsapp_imageAttachment || this.whatsapp_videoAttachment || this.whatsapp_documentAttachment || this.whatsapp_newMessage.trim();
        },
        filteredEmojis() {
            return this.emojis ? this.emojis[this.selectedCategory] : [];
        }
    },
});
</script>


