import { toast as sonnerToast } from 'vue-sonner';

export const toast = {
    success(title: string, description?: string) {
        return sonnerToast.success(title, {
            description: description,
        });
    },
    error(title: string, description?: string) {
        return sonnerToast.error(title, {
            description: description,
        });
    },
    info(title: string, description?: string) {
        return sonnerToast.info(title, {
            description: description,
        });
    },
    warning(title: string, description?: string) {
        return sonnerToast.warning(title, {
            description: description,
        });
    },
    dismiss(id?: string | number) {
        return sonnerToast.dismiss(id);
    },
};
