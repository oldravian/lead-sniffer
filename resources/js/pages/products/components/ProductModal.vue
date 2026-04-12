<script setup lang="ts">
import { computed, reactive, ref, watch } from 'vue';

import AlertError from '@/components/AlertError.vue';
import InputError from '@/components/InputError.vue';
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
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { getCsrfHeaders } from '@/composables/useApi';
import { store, update } from '@/routes/api/products';

const isOpen = defineModel<boolean>('open');

interface ProductPayload {
    name: string;
    url: string;
    description: string;
    keywords: string;
}

interface Product {
    id: number;
    name: string;
    url: string | null;
    description: string | null;
    keywords: string | null;
}

const props = defineProps<{
    appName: string;
    product?: Product | null;
}>();

const emptyForm: ProductPayload = {
    name: '',
    url: '',
    description: '',
    keywords: '',
};

const form = reactive<ProductPayload>({ ...emptyForm });
const errors = ref<Record<string, string>>({});
const isSubmitting = ref(false);

const emit = defineEmits<{
    (event: 'saved', payload: Product): void;
}>();

const isEditing = computed(() => Boolean(props.product?.id));

const modalCopy = computed(() => {
    if (isEditing.value) {
        return {
            title: 'Edit product',
            description: 'Update the details for this product.',
            buttonText: 'Save changes',
        };
    }

    return {
        title: 'Create product',
        description: `Add the product you want ${props.appName} to monitor for Reddit discussions.`,
        buttonText: 'Create product',
    };
});

const resetForm = () => {
    Object.assign(form, emptyForm);
};

const hydrateForm = () => {
    if (!props.product) {
        resetForm();
        return;
    }

    Object.assign(form, {
        name: props.product.name ?? '',
        url: props.product.url ?? '',
        description: props.product.description ?? '',
        keywords: props.product.keywords ?? '',
    });
};

const clearErrors = () => {
    errors.value = {};
};

const handleSubmit = async () => {
    if (isSubmitting.value) {
        return;
    }

    isSubmitting.value = true;
    clearErrors();

    const routeDefinition =
        isEditing.value && props.product
            ? update(props.product.id)
            : store();

    try {
        const response = await fetch(routeDefinition.url, {
            method: routeDefinition.method.toUpperCase(),
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                ...getCsrfHeaders(),
            },
            body: JSON.stringify(form),
        });

        if (!response.ok) {
            if (response.status === 422) {
                const data = await response.json();
                const fieldErrors = data?.errors ?? {};
                const formattedErrors: Record<string, string> = {};

                Object.keys(fieldErrors).forEach((key) => {
                    const message = fieldErrors[key]?.[0];
                    if (message) {
                        formattedErrors[key] = message;
                    }
                });

                errors.value = formattedErrors;
                return;
            }

            throw new Error(`Request failed: ${response.status}`);
        }

        const payload = (await response.json()) as Product;

        emit('saved', payload);
        isOpen.value = false;
        resetForm();
    } catch {
        errors.value = {
            form: 'Failed to save the product. Please try again.',
        };
    } finally {
        isSubmitting.value = false;
    }
};

watch(
    () => isOpen.value,
    (value) => {
        if (value) {
            hydrateForm();
        } else {
            clearErrors();
        }
    },
);

watch(
    () => props.product,
    () => {
        if (isOpen.value) {
            hydrateForm();
        }
    },
);
</script>

<template>
    <Dialog :open="isOpen" @update:open="isOpen = $event">
        <DialogContent class="sm:max-w-lg">
            <form class="space-y-6" @submit.prevent="handleSubmit">
                <DialogHeader class="space-y-2">
                    <DialogTitle>{{ modalCopy.title }}</DialogTitle>
                    <DialogDescription>
                        {{ modalCopy.description }}
                    </DialogDescription>
                </DialogHeader>

                <AlertError
                    v-if="errors.form"
                    :errors="[errors.form]"
                    title="Unable to save product"
                />

                <div class="grid gap-4">
                    <div class="grid gap-2">
                        <Label for="product-name">Name</Label>
                        <Input
                            id="product-name"
                            v-model="form.name"
                            placeholder="Acme CRM"
                            autocomplete="off"
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="product-url">Website</Label>
                        <Input
                            id="product-url"
                            v-model="form.url"
                            type="url"
                            placeholder="https://acmecrm.com"
                            autocomplete="off"
                        />
                        <InputError :message="errors.url" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="product-description">Description</Label>
                        <textarea
                            id="product-description"
                            v-model="form.description"
                            rows="4"
                            class="border-input focus-visible:border-ring focus-visible:ring-ring/50 aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive min-h-[120px] w-full rounded-md border bg-transparent px-3 py-2 text-sm shadow-xs outline-none focus-visible:ring-[3px] dark:bg-input/30"
                            placeholder="Short summary about what this product does"
                        />
                        <InputError :message="errors.description" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="product-keywords">Keywords</Label>
                        <Input
                            id="product-keywords"
                            v-model="form.keywords"
                            placeholder="lead gen, crm, b2b"
                            autocomplete="off"
                        />
                        <InputError :message="errors.keywords" />
                    </div>
                </div>

                <DialogFooter class="gap-2">
                    <DialogClose as-child>
                        <Button
                            type="button"
                            variant="secondary"
                            :disabled="isSubmitting"
                        >
                            Cancel
                        </Button>
                    </DialogClose>

                    <Button type="submit" :disabled="isSubmitting">
                        <Spinner v-if="isSubmitting" />
                        {{ modalCopy.buttonText }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
