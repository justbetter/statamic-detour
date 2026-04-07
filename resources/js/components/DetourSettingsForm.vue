<template>
    <form :action="action" method="POST" class="mt-6 space-y-4">
        <input type="hidden" name="_token" :value="csrfToken" />
        <input type="hidden" name="query_string_default_handling" :value="handlingValue" />

        <Field>
            <Label for="query_string_default_handling">
                {{ __('Default query string handling') }}
            </Label>
            <Select
                id="query_string_default_handling"
                class="w-full mt-2"
                :options="handlingOptions"
                v-model="handling"
            />
        </Field>

        <Field v-show="handlingValue === 'strip_specific_keys'">
            <Label for="query_string_default_strip_keys">
                {{ __('Default query keys to strip') }}
            </Label>
            <Input
                id="query_string_default_strip_keys"
                name="query_string_default_strip_keys"
                type="text"
                class="mt-2"
                v-model="stripKeysValue"
            />
            <p class="text-sm text-gray-700 dark:text-gray-300 mt-2">
                {{ __('Comma-separated list, e.g. gclid,fbclid') }}
            </p>
        </Field>

        <div class="flex items-center gap-3">
            <Button type="submit" variant="primary" :text="__('Save settings')" />
        </div>
    </form>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Button, Field, Input, Label, Select } from '@statamic/cms/ui';

const { selectedHandling, stripKeys } = defineProps({
    action: {
        type: String,
        required: true,
    },
    csrfToken: {
        type: String,
        required: true,
    },
    handlingOptions: {
        type: Array,
        required: true,
    },
    selectedHandling: {
        type: String,
        required: true,
    },
    stripKeys: {
        type: String,
        default: '',
    },
});

const handling = ref(selectedHandling);
const stripKeysValue = ref(stripKeys);
const handlingValue = computed(() => {
    if (typeof handling.value === 'string') {
        return handling.value;
    }

    if (handling.value && typeof handling.value === 'object' && 'value' in handling.value) {
        return handling.value.value;
    }

    return '';
});
</script>
