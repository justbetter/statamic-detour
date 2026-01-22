<template>
    <div>
        <publish-form :title="''" :action="action" :blueprint="blueprint" :meta="meta" :values="values"
                      @saved="addItem($event)"></publish-form>

        <table class="min-w-full border border-gray-200">
            <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">{{ __('From') }}</th>
                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">{{ __('To') }}</th>
                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">{{ __('Type') }}</th>
                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">{{ __('Code') }}</th>
                <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">{{ __('Sites') }}</th>
                <th class="px-4 py-3 text-right text-sm font-semibold text-gray-700">{{ __('Delete') }}</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
            <tr class="hover:bg-gray-50 text-gray-900" v-for="(item, id) in detours" :key="id">
                <td class="px-4 py-3 text-sm">{{ item.from }}</td>
                <td class="px-4 py-3 text-sm">{{ item.to }}</td>
                <td class="px-4 py-3 text-sm">{{ item.type }}</td>
                <td class="px-4 py-3 text-sm font-medium">{{ item.code }}</td>
                <td class="px-4 py-3 text-sm space-x-2">

                    <span class="rounded-full bg-green-500 text-white p-2" v-if="item.sites?.length === 0">{{ __('All') }}</span>
                    <span class="rounded-full bg-green-500 text-white p-2" v-for="site in item.sites" v-else :key="site">{{ site }}</span>
                </td>
                <td class="px-4 py-3 text-right">
                    <button
                        @click="deleteDetour(id)"
                        class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-red-500 rounded hover:bg-red-400 focus:outline-none focus:ring-2 focus:ring-red-500">
                        {{ __('Delete') }}
                    </button>
                </td>
            </tr>
            </tbody>
        </table>

        <nav class="mt-4 flex items-center justify-between" v-if="totalPages > 1">
            <div class="text-sm text-gray-700">
                {{ __('Page') }} {{ page }} {{ __('of') }} {{ totalPages }}
            </div>
            <div class="flex items-center gap-2">
                <button
                    class="px-2 py-2 text-sm font-medium border rounded disabled:opacity-50 flex items-center"
                    :disabled="page === 1"
                    @click="goToPage(1)"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-700">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m18.75 4.5-7.5 7.5 7.5 7.5m-6-15L5.25 12l7.5 7.5" />
                    </svg>
                </button>
                <button
                    class="px-2 py-2 text-sm font-medium border rounded disabled:opacity-50 flex items-center"
                    :disabled="page === 1"
                    @click="goToPage(page - 1)"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-700">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                </button>
                <button
                    v-for="pageLink in pageLinks"
                    :key="pageLink"
                    class="px-3 py-2 text-sm font-medium border rounded"
                    :class="pageLink === page ? 'bg-gray-900 text-white border-gray-900' : 'bg-white text-gray-700'"
                    @click="goToPage(pageLink)"
                >
                    {{ pageLink }}
                </button>
                <button
                    class="px-2 py-2 text-sm font-medium border rounded disabled:opacity-50 flex items-center"
                    :disabled="page === totalPages"
                    @click="goToPage(page + 1)"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-700">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </button>
                <button
                    class="px-2 py-2 text-sm font-medium border rounded disabled:opacity-50 flex items-center"
                    :disabled="page === totalPages"
                    @click="goToPage(totalPages)"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-700">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m5.25 4.5 7.5 7.5-7.5 7.5m6-15 7.5 7.5-7.5 7.5" />
                    </svg>
                </button>
            </div>
        </nav>
    </div>
</template>

<script>
export default ({
    props: {
        action: String,
        blueprint: Array,
        meta: Array,
        values: Array,
        data: Array,
        items: Array,
        page: Number,
        totalPages: Number,
    },

    data() {
        return {
            detours: [],
        }
    },

    mounted() {
        this.detours = this.items
    },

    computed: {
        pageLinks() {
            const current = this.page || 1;
            const last = this.totalPages || 1;
            const start = Math.max(1, current - 2);
            const end = Math.min(last, current + 2);
            const links = [];

            for (let p = start; p <= end; p++) {
                links.push(p);
            }

            return links;
        },
    },

    methods: {
        deleteItem(id, type = 'delete') {
            if (type == 'delete') {
                delete this.detours[id];
            }

            this.$forceUpdate();
        },

        addItem(event) {
            this.detours[event.data.id] = event.data;
            this.$forceUpdate();
        },

        deleteDetour(id) {
            const url = cp_url('/detours/' + id);
            Statamic.$axios.delete(url).then(() => this.deleteItem(id));
        },

        goToPage(page) {
            if (page === this.page || page < 1 || page > this.totalPages) {
                return;
            }

            const url = new URL(window.location.href);
            url.searchParams.set('page', page);
            window.location.href = url.toString();
        }
    }
})
</script>
