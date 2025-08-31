<script setup lang="ts">
import ChatList from '@/components/custom/sidebar/ChatList.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader } from '@/components/ui/sidebar';
import AppLogoSidebar from '@/components/custom/sidebar/AppLogoSidebar.vue';
import SidebarHeaderSearch from '@/components/custom/sidebar/SidebarHeaderSearch.vue';
import UserCard from '@/components/custom/sidebar/UserCard.vue';
import { usePage } from '@inertiajs/vue3'
import { ref, watch } from 'vue';

const page = usePage()
const user = ref(page.props.auth.user);

watch(
    () => page.props.auth.user,
    (changedUser) => {
        user.value = changedUser;
    }
);
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
