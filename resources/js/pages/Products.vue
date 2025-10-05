<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import LoadingOverlay from '@/components/LoadingOverlay.vue';
import Pagination from '@/components/Pagination.vue';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';

type Product = {
  id: number;
  name: string;
  slug: string;
  description?: string | null;
  price: number;
  compare_at_price?: number | null;
  stock: number;
  is_active: boolean;
  categories: { id: number; name: string }[];
  images?: { id: number; url: string }[];
};

const page = usePage();
const paginator = computed<any>(() => (page.props as any).products);
const products = computed<Product[]>(() => (paginator.value?.data ?? []) as Product[]);
const allCategories = computed<{ id: number; name: string }[]>(() => ((page.props as any).allCategories ?? []) as any);
const filters = computed<any>(() => (page.props as any).filters ?? {});

const isDialogOpen = ref(false);
const isSaving = ref(false);
const replaceImages = ref(false);

const form = useForm<{ 
  id?: number;
  name: string;
  slug?: string;
  description?: string;
  price: number | string;
  compare_at_price?: number | string | null;
  stock: number | string;
  is_active: boolean | number | string;
  category_ids: number[];
  images: File[];
}>({
  name: '', slug: '', description: '', price: '', compare_at_price: '', stock: '', is_active: true, category_ids: [], images: [],
});

function openCreate() {
  form.reset();
  form.clearErrors();
  replaceImages.value = false;
  form.name = '';
  form.slug = '';
  form.description = '';
  form.price = '';
  form.compare_at_price = '';
  form.stock = '';
  form.is_active = true;
  form.category_ids = [];
  form.images = [];
  isDialogOpen.value = true;
}

function openEdit(p: Product) {
  form.clearErrors();
  replaceImages.value = false;
  form.id = p.id;
  form.name = p.name;
  form.slug = p.slug;
  form.description = p.description || '';
  form.price = String(p.price);
  form.compare_at_price = p.compare_at_price != null ? String(p.compare_at_price) : '';
  form.stock = String(p.stock);
  form.is_active = p.is_active;
  form.category_ids = p.categories.map(c => c.id);
  form.images = [];
  isDialogOpen.value = true;
}

async function save() {
  isSaving.value = true;
  try {
    const isUpdate = !!form.id;
    const url = isUpdate ? `/products/${form.id}` : '/products';
    const postOptions = {
      forceFormData: true,
      onSuccess: () => {
        import('@/composables/useToast').then(({ useToast }) => useToast().success(isUpdate ? 'Product updated' : 'Product created'));
        isDialogOpen.value = false;
        router.reload({ only: ['products'] });
      },
      onError: () => {
        import('@/composables/useToast').then(({ useToast }) => useToast().error('Validation failed'));
      },
    } as const;

    if (isUpdate) {
      form.transform((data) => ({ ...data, _method: 'put', is_active: data.is_active ? 1 : 0, replace_images: replaceImages.value ? 1 : 0 }));
      await form.post(url, postOptions as any);
    } else {
      form.transform((data) => ({ ...data, is_active: data.is_active ? 1 : 0 }));
      await form.post(url, postOptions as any);
    }
  } finally {
    isSaving.value = false;
  }
}

async function remove(p: Product) {
  if (!confirm(`Delete product "${p.name}"?`)) return;
  const helper = useForm({});
  await new Promise<void>((resolve) => {
    helper.delete(`/products/${p.id}`, {
      onSuccess: () => {
        import('@/composables/useToast').then(({ useToast }) => useToast().success('Product deleted'));
        router.reload({ only: ['products'] });
        resolve();
      },
      onError: () => {
        import('@/composables/useToast').then(({ useToast }) => useToast().error('Delete failed'));
        resolve();
      },
    });
  });
}
</script>

<template>
  <Head title="Products" />
  <AppLayout :breadcrumbs="[{ title: 'Products', href: '/products' }]"><!-- page content -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-2">
        <input :value="filters.search || ''" @input="e => router.visit('/products', { method: 'get', data: { search: (e.target as HTMLInputElement).value }, preserveState: true, only: ['products','filters'] })" placeholder="Search..." class="w-64 rounded border px-3 py-2" />
        <select :value="filters.categoryId || ''" @change="e => router.visit('/products', { method: 'get', data: { category_id: (e.target as HTMLSelectElement).value }, preserveState: true, only: ['products','filters'] })" class="rounded border px-3 py-2">
          <option value="">All categories</option>
          <option v-for="c in allCategories" :key="c.id" :value="c.id">{{ c.name }}</option>
        </select>
      </div>
      <Button @click="openCreate">Add Product</Button>
    </div>

    <div class="mt-4 overflow-hidden rounded border">
      <table class="min-w-full divide-y">
        <thead class="bg-neutral-50">
          <tr>
            <th class="px-3 py-2 text-left text-sm font-semibold">Image</th>
            <th class="px-3 py-2 text-left text-sm font-semibold">Name</th>
            <th class="px-3 py-2 text-left text-sm font-semibold">Price</th>
            <th class="px-3 py-2 text-left text-sm font-semibold">Stock</th>
            <th class="px-3 py-2 text-left text-sm font-semibold">Categories</th>
            <th class="px-3 py-2 text-right text-sm font-semibold">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="p in products" :key="p.id">
            <td class="px-3 py-2">
              <img v-if="p.images && p.images.length" :src="p.images[0].url" alt="" class="h-10 w-10 rounded object-cover" />
            </td>
            <td class="px-3 py-2">{{ p.name }}</td>
            <td class="px-3 py-2">{{ p.price }}</td>
            <td class="px-3 py-2">{{ p.stock }}</td>
            <td class="px-3 py-2">{{ p.categories.map(c => c.name).join(', ') }}</td>
            <td class="px-3 py-2 text-right">
              <Button variant="secondary" class="mr-2" @click="openEdit(p)">Edit</Button>
              <Button variant="destructive" @click="remove(p)">Delete</Button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <Pagination v-if="paginator" :links="paginator.links" />

    <Dialog :open="isDialogOpen" @update:open="isDialogOpen = $event">
      <DialogContent class="sm:max-w-xl">
        <DialogHeader>
          <DialogTitle>{{ form.id ? 'Edit Product' : 'Add Product' }}</DialogTitle>
        </DialogHeader>
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-sm">Name</label>
            <input v-model="form.name" class="w-full rounded border px-3 py-2" />
            <div v-if="form.errors.name" class="text-sm text-red-600">{{ form.errors.name }}</div>
          </div>
          <div>
            <label class="block text-sm">Slug (optional)</label>
            <input v-model="form.slug" class="w-full rounded border px-3 py-2" />
            <div v-if="form.errors.slug" class="text-sm text-red-600">{{ form.errors.slug }}</div>
          </div>
          <div class="col-span-2">
            <label class="block text-sm">Description</label>
            <textarea v-model="form.description" class="w-full rounded border px-3 py-2"></textarea>
          </div>
          <div>
            <label class="block text-sm">Price</label>
            <input type="number" step="0.01" v-model="form.price" class="w-full rounded border px-3 py-2" />
            <div v-if="form.errors.price" class="text-sm text-red-600">{{ form.errors.price }}</div>
          </div>
          <div>
            <label class="block text-sm">Compare at price</label>
            <input type="number" step="0.01" v-model.number="form.compare_at_price" class="w-full rounded border px-3 py-2" />
          </div>
          <div>
            <label class="block text-sm">Stock</label>
            <input type="number" v-model="form.stock" class="w-full rounded border px-3 py-2" />
            <div v-if="form.errors.stock" class="text-sm text-red-600">{{ form.errors.stock }}</div>
          </div>
          <div class="flex items-center gap-2">
            <input id="active" type="checkbox" v-model="form.is_active" />
            <label for="active">Active</label>
          </div>
          <div class="col-span-2">
            <label class="block text-sm">Categories</label>
            <select multiple v-model="form.category_ids" class="w-full rounded border px-3 py-2 h-28">
              <option v-for="c in allCategories" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
            <div v-if="form.errors['category_ids.0']" class="text-sm text-red-600">{{ form.errors['category_ids.0'] }}</div>
          </div>
          <div class="col-span-2">
            <label class="block text-sm">Images</label>
            <input type="file" multiple @change="e => form.images = Array.from((e.target as HTMLInputElement).files || [])" class="w-full" />
            <div v-if="form.errors['images.0']" class="text-sm text-red-600">{{ form.errors['images.0'] }}</div>
            <div v-if="form.id && (products.find(x => x.id === form.id)?.images?.length)">
              <div class="mt-2 flex flex-wrap gap-2">
                <img v-for="img in (products.find(x => x.id === form.id)?.images || [])" :key="img.id" :src="img.url" class="h-14 w-14 rounded object-cover" />
              </div>
              <label class="mt-2 inline-flex items-center gap-2 text-sm">
                <input type="checkbox" v-model="replaceImages" /> Replace existing images
              </label>
            </div>
          </div>
          <div class="col-span-2 flex justify-end gap-2 pt-2">
            <Button variant="ghost" @click="isDialogOpen = false">Cancel</Button>
            <Button @click="save">{{ form.id ? 'Update' : 'Create' }}</Button>
          </div>
        </div>
        <LoadingOverlay v-if="isSaving" :message="form.id ? 'Updating product' : 'Adding product'" />
      </DialogContent>
    </Dialog>

  </AppLayout>
</template>


