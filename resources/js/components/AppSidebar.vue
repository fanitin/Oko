<script setup lang="ts">
import ChatList from '@/components/custom/sidebar/ChatList.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader } from '@/components/ui/sidebar';
import AppLogoSidebar from '@/components/custom/sidebar/AppLogoSidebar.vue';
import SidebarHeaderSearch from '@/components/custom/sidebar/SidebarHeaderSearch.vue';
import UserCard from '@/components/custom/sidebar/UserCard.vue';
import { usePage } from '@inertiajs/vue3'

const page = usePage()
const user = page.props.auth.user
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <AppLogoSidebar/>
            <SidebarHeaderSearch/>
            <UserCard :user="user"/>
        </SidebarHeader>

        <SidebarContent>
            <ChatList />
        </SidebarContent>

        <SidebarFooter></SidebarFooter>
    </Sidebar>
    <slot />
</template>
