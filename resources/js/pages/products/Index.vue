<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Pencil, Trash2 } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

import ConfirmDialog from '@/components/ConfirmDialog.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { getCsrfHeaders } from '@/composables/useApi';
import AppLayout from '@/layouts/AppLayout.vue';
import ProductModal from '@/pages/products/components/ProductModal.vue';
import { destroy } from '@/routes/api/products';
import { type BreadcrumbItem } from '@/types';

interface Product {
    id: number;
    name: string;
    url: string | null;
    description: string | null;
    keywords: string | null;
}

const props = defineProps<{
    appName: string;
    products: Product[];
}>();

const products = ref<Product[]>([...props.products]);

const description = computed(
    () =>
        `These are the products ${props.appName} monitors on Reddit to surface relevant leads and discussions.`,
);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Products',
        href: '/products',
    },
];

const isProductModalOpen = ref(false);
const selectedProduct = ref<Product | null>(null);
const isDeleteDialogOpen = ref(false);
const productToDelete = ref<Product | null>(null);
const isDeleting = ref(false);
const deleteError = ref<string | null>(null);

const handleProductSaved = (product: Product) => {
    const existingIndex = products.value.findIndex(
        (existing) => existing.id === product.id,
    );

    if (existingIndex >= 0) {
        products.value[existingIndex] = product;
        return;
    }

    products.value = [product, ...products.value];
};

const handleCreateProduct = () => {
    selectedProduct.value = null;
    isProductModalOpen.value = true;
};

const handleEditProduct = (product: Product) => {
    selectedProduct.value = product;
    isProductModalOpen.value = true;
};

const handleDeleteProduct = (product: Product) => {
    productToDelete.value = product;
    deleteError.value = null;
    isDeleteDialogOpen.value = true;
};

const handleDeleteConfirm = async () => {
    if (!productToDelete.value || isDeleting.value) {
        return;
    }

    isDeleting.value = true;
    deleteError.value = null;

    const target = productToDelete.value;
    const routeDefinition = destroy(target.id);

    try {
        const response = await fetch(routeDefinition.url, {
            method: routeDefinition.method.toUpperCase(),
            headers: {
                Accept: 'application/json',
                ...getCsrfHeaders(),
            },
        });

        if (!response.ok) {
            throw new Error(`Request failed: ${response.status}`);
        }

        products.value = products.value.filter(
            (product) => product.id !== target.id,
        );

        isDeleteDialogOpen.value = false;
        productToDelete.value = null;
    } catch {
        deleteError.value = 'Failed to delete the product. Please try again.';
    } finally {
        isDeleting.value = false;
    }
};

const handleDeleteCancel = () => {
    if (isDeleting.value) {
        return;
    }

    isDeleteDialogOpen.value = false;
    productToDelete.value = null;
    deleteError.value = null;
};

const deleteDialogTitle = computed(() => {
    if (productToDelete.value) {
        return `Delete ${productToDelete.value.name}?`;
    }

    return 'Delete product?';
});

const deleteDialogDescription = computed(() => {
    if (deleteError.value) {
        return deleteError.value;
    }

    if (productToDelete.value) {
        return `This will permanently delete ${productToDelete.value.name} and remove it from your products.`;
    }

    return 'This action cannot be undone.';
});

watch(
    () => props.products,
    (value) => {
        products.value = [...value];
    },
);

watch(
    () => isProductModalOpen.value,
    (value) => {
        if (!value) {
            selectedProduct.value = null;
        }
    },
);

watch(
    () => isDeleteDialogOpen.value,
    (value) => {
        if (!value && !isDeleting.value) {
            productToDelete.value = null;
            deleteError.value = null;
        }
    },
);
</script>

<template>
    <Head title="Products" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-1 flex-col gap-6 p-6">
            <div
                class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between"
            >
                <Heading title="Products" :description="description" />
                <Button class="w-full sm:w-auto" @click="handleCreateProduct">
                    Create product
                </Button>
            </div>

            <div v-if="products.length" class="grid gap-5 md:grid-cols-2">
                <Card
                    v-for="product in products"
                    :key="product.id"
                    class="transition-shadow hover:shadow-md"
                >
                    <CardHeader class="space-y-2">
                        <div class="flex items-start justify-between gap-4">
                            <CardTitle class="text-base font-semibold">
                                {{ product.name }}
                            </CardTitle>
                            <div class="flex items-center gap-2">
                                <Button
                                    variant="outline"
                                    size="icon"
                                    class="h-8 w-8 border-sky-500/40 text-sky-600 hover:border-sky-500 hover:text-sky-700"
                                    @click="handleEditProduct(product)"
                                >
                                    <Pencil class="h-4 w-4" />
                                    <span class="sr-only">
                                        Edit {{ product.name }}
                                    </span>
                                </Button>
                                <Button
                                    variant="outline"
                                    size="icon"
                                    class="h-8 w-8 border-rose-500/40 text-rose-600 hover:border-rose-500 hover:text-rose-700"
                                    @click="handleDeleteProduct(product)"
                                >
                                    <Trash2 class="h-4 w-4" />
                                    <span class="sr-only">
                                        Delete {{ product.name }}
                                    </span>
                                </Button>
                            </div>
                        </div>
                        <CardDescription class="truncate">
                            {{ product.url ?? 'No website added yet' }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <p class="text-sm text-muted-foreground">
                            {{
                                product.description ??
                                'Add a short description to keep this product easy to spot.'
                            }}
                        </p>
                    </CardContent>
                    <CardFooter class="text-xs text-muted-foreground">
                        <span class="truncate">
                            Keywords:
                            {{ product.keywords ?? 'Not set' }}
                        </span>
                    </CardFooter>
                </Card>
            </div>

            <div
                v-else
                class="flex flex-col items-start gap-4 rounded-xl border border-dashed border-border bg-muted/30 p-8"
            >
                <div class="space-y-1">
                    <h3 class="text-base font-semibold">No products yet</h3>
                    <p class="text-sm text-muted-foreground">
                        Add your first product to start finding leads.
                    </p>
                </div>
                <Button @click="handleCreateProduct"> Create product </Button>
            </div>
        </div>

        <ProductModal
            v-model:open="isProductModalOpen"
            :app-name="props.appName"
            :product="selectedProduct"
            @saved="handleProductSaved"
        />

        <ConfirmDialog
            v-model:open="isDeleteDialogOpen"
            :title="deleteDialogTitle"
            :description="deleteDialogDescription"
            confirm-text="Delete product"
            cancel-text="Cancel"
            :loading="isDeleting"
            @confirm="handleDeleteConfirm"
            @cancel="handleDeleteCancel"
        />
    </AppLayout>
</template>
