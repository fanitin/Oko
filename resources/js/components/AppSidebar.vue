<script setup lang="ts">
import ChatList from '@/components/custom/sidebar/ChatList.vue';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarTrigger } from '@/components/ui/sidebar';
import AppLogoSidebar from '@/components/custom/sidebar/AppLogoSidebar.vue';
import SidebarHeaderSearch from '@/components/custom/sidebar/SidebarHeaderSearch.vue';
import UserCard from '@/components/custom/sidebar/UserCard.vue';
import { usePage } from '@inertiajs/vue3'
import { ref, watch, onMounted, onUnmounted } from 'vue';
import { useSidebar } from '@/components/ui/sidebar/utils'

const page = usePage()
const user = ref(page.props.auth.user);
const { state, setOpen } = useSidebar()
const isMobile = ref(false);

watch(
    () => page.props.auth.user,
    (changedUser) => {
        user.value = changedUser;
    }
);

const checkMobile = () => {
    const wasMobile = isMobile.value;
    isMobile.value = window.innerWidth < 1024;

    if (isMobile.value && !wasMobile) {
        setOpen(true);
    }
};

onMounted(() => {
    checkMobile();
    window.addEventListener('resize', checkMobile);
});

onUnmounted(() => {
    window.removeEventListener('resize', checkMobile);
});

watch(isMobile, (newValue) => {
    if (newValue) {
        setOpen(true);
    }
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <div
                class="flex w-full flex-col gap-2"
                :class="state === 'expanded' ? 'flex-row items-center justify-between' : 'flex-col'"
            >
                <AppLogoSidebar />
                <SidebarTrigger />
            </div>

            <SidebarHeaderSearch />
            <UserCard :user="user"/>
        </SidebarHeader>

        <div class="my-2 h-px w-full bg-gray-300 dark:bg-gray-700"></div>

        <SidebarContent>
            <ChatList />
        </SidebarContent>

        <SidebarFooter></SidebarFooter>
    </Sidebar>
</template>
