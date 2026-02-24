<template>
    <div>
        <DetourForm
            :action="action"
            :blueprint="blueprint"
            :meta="meta"
            :values="values"
            @saved="addItem"
        />
        <Card>
            <Table class="mt-6 min-w-full">
                <TableRow>
                    <TableCell class="font-semibold">{{ __('From') }}</TableCell>
                    <TableCell class="font-semibold">{{ __('To') }}</TableCell>
                    <TableCell class="font-semibold">{{ __('Type') }}</TableCell>
                    <TableCell class="font-semibold">{{ __('Code') }}</TableCell>
                    <TableCell class="font-semibold">{{ __('Sites') }}</TableCell>
                    <TableCell class="text-right font-semibold">{{ __('Delete') }}</TableCell>
                </TableRow>
                <TableRow
                    v-for="item in detours"
                    :key="item.id"
                    class="hover:bg-gray-50"
                >
                    <TableCell>{{ item.from }}</TableCell>
                    <TableCell>{{ item.to }}</TableCell>
                    <TableCell>{{ item.type }}</TableCell>
                    <TableCell class="font-medium">{{ item.code }}</TableCell>
                    <TableCell class="space-x-2">
                        <Badge
                            v-if="item.sites?.length === 0"
                            color="green"
                        >
                            {{ __('All') }}
                        </Badge>
                        <Badge
                            v-for="site in item.sites"
                            v-else
                            :key="site"
                            color="green"
                        >
                            {{ site }}
                        </Badge>
                    </TableCell>
                    <TableCell class="text-right">
                        <Button
                            variant="danger"
                            size="sm"
                            :text="__('Delete')"
                            @click="deleteDetour(item.id)"
                        />
                    </TableCell>
                </TableRow>
            </Table>
        </Card>
        <div v-if="paginatorMeta" class="mt-4">
            <Pagination
                :resource-meta="paginatorMeta"
                :per-page="perPage"
                @page-selected="selectPage"
                @per-page-changed="selectPerPage"
            />
        </div>
    </div>
</template>
<script setup>
import { ref, onMounted } from 'vue';
import DetourForm from './DetourForm.vue';
import {
    Table,
    TableRow,
    TableCell,
    Button,
    Badge,
    Card,
    Pagination,
} from '@statamic/cms/ui';

const {
    action,
    blueprint,
    meta,
    values,
    items,
    paginatorMeta,
    perPage,
    indexUrl,
} = defineProps({
    action: {
        type: String,
        required: true,
    },
    blueprint: {
        type: Object,
        required: true,
    },
    meta: {
        type: Object,
        required: true,
    },
    values: {
        type: Object,
        default: () => ({}),
    },
    data: {
        type: Array,
        default: () => [],
    },
    items: {
        type: Array,
        default: () => [],
    },
    paginatorMeta: {
        type: Object,
        default: null,
    },
    perPage: {
        type: Number,
        default: 15,
    },
    indexUrl: {
        type: String,
        default: '',
    },
});

const detours = ref([]);

onMounted(() => {
    detours.value = items ?? [];
});

const buildPaginatedUrl = (page, size) => {
    const url = new URL(indexUrl, window.location.origin);
    url.searchParams.set('size', size);
    url.searchParams.set('page', page);
    return url.toString();
};

const selectPage = (page) => {
    window.location.href = buildPaginatedUrl(page, perPage);
};

const selectPerPage = (size) => {
    window.location.href = buildPaginatedUrl(1, size);
};

const addItem = (data) => {
    detours.value.push(data);
};

const deleteItem = (id) => {
    const index = detours.value.findIndex((item) => item.id === id);
    detours.value.splice(index, 1);
};

const deleteDetour = (id) => {
    const url = cp_url('/detours/' + id);
    Statamic.$axios.delete(url).then(() => deleteItem(id));
};
</script>