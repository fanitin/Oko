<script setup lang="ts">
import ChatList from '@/components/custom/sidebar/ChatList.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader } from '@/components/ui/sidebar';
import AppLogoSidebar from '@/components/custom/sidebar/AppLogoSidebar.vue';
import SidebarHeaderSearch from '@/components/custom/sidebar/SidebarHeaderSearch.vue';
import UserCard from '@/components/custom/sidebar/UserCard.vue';
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <AppLogoSidebar/>
            <SidebarHeaderSearch/>
            <UserCard/>
        </SidebarHeader>

        <SidebarContent>
            <ChatList />
        </SidebarContent>

        <SidebarFooter></SidebarFooter>
    </Sidebar>
    <slot />
</template>
