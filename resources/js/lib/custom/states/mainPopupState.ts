import { reactive } from 'vue';

export const mainPopupState = reactive({
    isVisible: false,
    message: '',
    type: 'info' as string,
    duration: 3000,
    timeoutId: null as number | null,

    show(message: string, type: string = 'info', duration = 3000) {
        this.message = message;
        this.type = type;
        this.duration = duration;
        this.isVisible = true;

        if (this.timeoutId) {
            clearTimeout(this.timeoutId);
        }

        this.timeoutId = setTimeout(() => {
            this.isVisible = false;
        }, this.duration);
    },

    hide() {
        this.isVisible = false;
        if (this.timeoutId) {
            clearTimeout(this.timeoutId);
        }
    }
});
