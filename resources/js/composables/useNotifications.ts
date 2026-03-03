import { ref, onMounted, onBeforeUnmount } from 'vue';

interface NotificationOptions {
    sound?: boolean;
    titleBlink?: boolean;
}

export function useNotifications() {
    const notificationSound = ref<HTMLAudioElement | null>(null);
    const originalTitle = ref<string>('');
    const blinkInterval = ref<number | null>(null);
    const unreadCount = ref<number>(0);
    const isWindowFocused = ref<boolean>(true);

    onMounted(() => {
        notificationSound.value = new Audio('/notification.mp3');
        notificationSound.value.volume = 0.5;

        originalTitle.value = document.title;

        window.addEventListener('focus', handleFocus);
        window.addEventListener('blur', handleBlur);

        isWindowFocused.value = document.hasFocus();
    });

    onBeforeUnmount(() => {
        window.removeEventListener('focus', handleFocus);
        window.removeEventListener('blur', handleBlur);
        stopTitleBlink();
    });

    const handleFocus = () => {
        isWindowFocused.value = true;
        stopTitleBlink();
        resetTitle();
    };

    const handleBlur = () => {
        isWindowFocused.value = false;
    };

    const playSound = () => {
        if (notificationSound.value) {
            notificationSound.value.currentTime = 0;
            notificationSound.value.play().catch((error) => {
                console.error('Failed to play notification sound:', error);
            });
        }
    };

    const startTitleBlink = () => {
        stopTitleBlink();

        let showMessage = true;

        const blink = () => {
            if (showMessage) {
                const count = unreadCount.value;
                document.title = `🔔 ${count} new message${count > 1 ? 's' : ''}`;
                blinkInterval.value = window.setTimeout(blink, 2000);
            } else {
                document.title = originalTitle.value;
                blinkInterval.value = window.setTimeout(blink, 1500);
            }
            showMessage = !showMessage;
        };

        blink();
    };

    const stopTitleBlink = () => {
        if (blinkInterval.value !== null) {
            clearTimeout(blinkInterval.value);
            blinkInterval.value = null;
        }
    };

    const resetTitle = () => {
        document.title = originalTitle.value;
        unreadCount.value = 0;
    };

    const notify = (options: NotificationOptions = {}) => {
        const { sound = true, titleBlink = true } = options;

        if (!isWindowFocused.value) {
            if (sound) {
                playSound();
            }

            unreadCount.value++;

            if (titleBlink) {
                startTitleBlink();
            }
        }
    };

    const setOriginalTitle = (title: string) => {
        originalTitle.value = title;
        if (isWindowFocused.value) {
            document.title = title;
        }
    };

    return {
        notify,
        resetTitle,
        isWindowFocused,
        unreadCount,
        setOriginalTitle,
    };
}
