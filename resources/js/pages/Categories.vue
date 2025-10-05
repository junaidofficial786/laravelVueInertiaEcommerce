<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import LoadingOverlay from '@/components/LoadingOverlay.vue';
import Pagination from '@/components/Pagination.vue';
import { Dialog, DialogContent, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';

type Category = {
  id: number;
  name: string;
  slug: string;
  description?: string | null;
  is_active: boolean;
  sort_order: number;
};

const page = usePage();
const paginator = computed<any>(() => (page.props as any).categories);
const categories = computed<Category[]>(() => (paginator.value?.data ?? []) as Category[]);
const isFetching = ref(false);
const isDialogOpen = ref(false);
const form = ref<Partial<Category>>({ name: '', description: '', is_active: true, sort_order: 0 });
const isSaving = ref(false);
const formErrors = ref<Record<string, string[]>>({});
const search = ref('');

async function fetchCategories() { /* now provided via Inertia props on index */ }

function openCreate() {
  form.value = { name: '', description: '', is_active: true, sort_order: 0 };
  isDialogOpen.value = true;
}

function openEdit(cat: Category) {
  form.value = { ...cat };
  isDialogOpen.value = true;
}

async function save() {
  isSaving.value = true;
  try {
    const isUpdate = !!form.value.id;
    const payload = {
      name: form.value.name,
      description: form.value.description,
      is_active: form.value.is_active,
      sort_order: form.value.sort_order,
      slug: form.value.slug,
    };
    const formHelper = useForm(payload as any);
    const route = isUpdate ? `/categories/${form.value.id}` : '/categories';
    const method: 'post' | 'put' = isUpdate ? 'put' : 'post';
    await new Promise<void>((resolve) => {
      formHelper[method](route, {
        onSuccess: () => {
          import('@/composables/useToast').then(({ useToast }) => useToast().success(isUpdate ? 'Category updated' : 'Category created'));
          isDialogOpen.value = false;
          router.reload({ only: ['categories'] });
          resolve();
        },
        onError: (errs: Record<string, string[]>) => {
          formErrors.value = errs;
          import('@/composables/useToast').then(({ useToast }) => useToast().error('Validation failed'));
          resolve();
        },
      });
    });
  } finally {
    isSaving.value = false;
  }
}

async function remove(cat: Category) {
  if (!confirm(`Delete category "${cat.name}"?`)) return;
  isSaving.value = true;
  try {
    const formHelper = useForm({});
    await new Promise<void>((resolve) => {
      formHelper.delete(`/categories/${cat.id}`, {
        onSuccess: () => {
          import('@/composables/useToast').then(({ useToast }) => useToast().success('Category deleted'));
          router.reload({ only: ['categories'] });
          resolve();
        },
        onError: () => {
          import('@/composables/useToast').then(({ useToast }) => useToast().error('Delete failed'));
          resolve();
        },
      });
    });
  } catch (e: any) {
    const Toaster = (await import('@/composables/useToast')).useToast();
    Toaster.error(e?.message || 'Delete failed');
  } finally {
    isSaving.value = false;
  }
}

watch(search, fetchCategories);
fetchCategories();
</script>

<template>
  <Head title="Categories" />
  <AppLayout :breadcrumbs="[{ title: 'Categories', href: '/categories' }]"><!-- page content -->
    <div class="flex items-center justify-between">
      <input v-model="search" placeholder="Search..." class="w-64 rounded border px-3 py-2" />
      <Button @click="openCreate">Add Category</Button>
    </div>

    <div class="mt-4 overflow-hidden rounded border">
      <table class="min-w-full divide-y">
        <thead class="bg-neutral-50">
          <tr>
            <th class="px-3 py-2 text-left text-sm font-semibold">Name</th>
            <th class="px-3 py-2 text-left text-sm font-semibold">Slug</th>
            <th class="px-3 py-2 text-left text-sm font-semibold">Active</th>
            <th class="px-3 py-2 text-right text-sm font-semibold">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <tr v-for="cat in categories" :key="cat.id">
            <td class="px-3 py-2">{{ cat.name }}</td>
            <td class="px-3 py-2">{{ cat.slug }}</td>
            <td class="px-3 py-2">{{ cat.is_active ? 'Yes' : 'No' }}</td>
            <td class="px-3 py-2 text-right">
              <Button variant="secondary" class="mr-2" @click="openEdit(cat)">Edit</Button>
              <Button variant="destructive" @click="remove(cat)">Delete</Button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <Pagination v-if="paginator" :links="paginator.links" />

    <Dialog :open="isDialogOpen" @update:open="isDialogOpen = $event">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>{{ form.id ? 'Edit Category' : 'Add Category' }}</DialogTitle>
        </DialogHeader>
        <div class="space-y-3">
          <div class="space-y-1">
            <label class="block text-sm">Name</label>
            <input v-model="form.name" class="w-full rounded border px-3 py-2" />
          </div>
          <div class="space-y-1">
            <label class="block text-sm">Slug (optional)</label>
            <input v-model="form.slug" class="w-full rounded border px-3 py-2" />
            <div v-if="formErrors.slug" class="text-sm text-red-600">{{ formErrors.slug[0] }}</div>
          </div>
          <div class="space-y-1">
            <label class="block text-sm">Description</label>
            <textarea v-model="form.description" class="w-full rounded border px-3 py-2"></textarea>
          </div>
          <div class="flex items-center gap-2">
            <input id="active" type="checkbox" v-model="form.is_active" />
            <label for="active">Active</label>
          </div>
          <div class="space-y-1">
            <label class="block text-sm">Sort Order</label>
            <input type="number" v-model.number="form.sort_order" class="w-32 rounded border px-3 py-2" />
          </div>
          <div class="flex justify-end gap-2 pt-2">
            <Button variant="ghost" @click="isDialogOpen = false">Cancel</Button>
            <Button @click="save">{{ form.id ? 'Update' : 'Create' }}</Button>
          </div>
        </div>
        <LoadingOverlay v-if="isSaving" :message="form.id ? 'Updating category' : 'Adding category'" />
      </DialogContent>
    </Dialog>

    <LoadingOverlay v-if="isFetching" message="Loading categories" />
  </AppLayout>
</template>


