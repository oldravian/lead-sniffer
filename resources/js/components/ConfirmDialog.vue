<script setup lang="ts">
import { computed } from 'vue';

import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Spinner } from '@/components/ui/spinner';

const isOpen = defineModel<boolean>('open');

const props = withDefaults(
    defineProps<{
        title: string;
        description?: string;
        confirmText?: string;
        cancelText?: string;
        loading?: boolean;
    }>(),
    {
        description: '',
        confirmText: 'Confirm',
        cancelText: 'Cancel',
        loading: false,
    },
);

const emit = defineEmits<{
    (event: 'confirm'): void;
    (event: 'cancel'): void;
}>();

const isBusy = computed(() => Boolean(props.loading));

const handleConfirm = () => {
    emit('confirm');
};

const handleCancel = () => {
    emit('cancel');
};
</script>

<template>
    <Dialog :open="isOpen" @update:open="isOpen = $event">
        <DialogContent class="sm:max-w-md">
            <DialogHeader class="space-y-2">
                <DialogTitle>{{ props.title }}</DialogTitle>
                <DialogDescription v-if="props.description">
                    {{ props.description }}
                </DialogDescription>
            </DialogHeader>

            <DialogFooter class="gap-2">
                <DialogClose as-child>
                    <Button
                        type="button"
                        variant="ghost"
                        :disabled="isBusy"
                        @click="handleCancel"
                    >
                        {{ props.cancelText }}
                    </Button>
                </DialogClose>

                <Button
                    type="button"
                    variant="destructive"
                    :disabled="isBusy"
                    @click="handleConfirm"
                >
                    <Spinner v-if="isBusy" class="mr-2" />
                    {{ props.confirmText }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
