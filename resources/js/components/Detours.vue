<template>
    <div>
        <publish-form :title="''" :action="action" :blueprint="blueprint" :meta="meta" :values="values" @saved="addItem($event)"></publish-form>
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
                <tr class="hover:bg-gray-50 text-gray-900" v-for="(item, key) in detours" :key="item.id">
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
                            @click="deleteDetour(item.id)"
                            class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-white bg-red-500 rounded hover:bg-red-400 focus:outline-none focus:ring-2 focus:ring-red-500">
                            {{ __('Delete') }}
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
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
    },

    data() {
        return {
            detours: [],
        }
    },

    mounted() {
        this.detours = this.items
    },

    methods: {
        deleteItem(id) {
            const index = this.detours.findIndex(item => item.id === id);
            this.detours.splice(index, 1);

            this.$forceUpdate();
        },

        addItem(event) {
            this.detours.push(event.data);
            this.$forceUpdate();
        },

        deleteDetour(id) {
            const url = cp_url('/detours/' + id);
            Statamic.$axios.delete(url).then(() => this.deleteItem(id));
        },
    }
})
</script>
