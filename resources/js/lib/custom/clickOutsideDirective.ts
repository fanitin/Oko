import type { Directive } from 'vue';

interface ClickOutsideBinding {
    handler: (e: MouseEvent) => void;
    ignore?: { value: HTMLElement | null };
}

const clickOutsideDirective: Directive<HTMLElement, ClickOutsideBinding> = {
    mounted(el, binding) {
        const { handler, ignore } = binding.value;

        const clickOutsideEvent = (event: MouseEvent) => {
            const target = event.target as Node;
            const ignoreEl = ignore?.value;

            if (!(el === target || el.contains(target) || ignoreEl?.contains(target))) {
                handler(event);
            }
        };

        (el as any).__clickOutsideEvent__ = clickOutsideEvent;

        setTimeout(() => {
            document.addEventListener('click', clickOutsideEvent);
        }, 0);
    },
    unmounted(el) {
        const clickOutsideEvent = (el as any).__clickOutsideEvent__;
        if (clickOutsideEvent) {
            document.removeEventListener('click', clickOutsideEvent);
        }
    },
};

export default clickOutsideDirective;
