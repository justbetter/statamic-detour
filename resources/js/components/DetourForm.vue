<template>
    <Header :title="__('Add Detour')" icon="link">
        <Button variant="primary" :text="__('Save')" @click="save" :disabled="saving" />
    </Header>
    <PublishContainer
        ref="container"
        :name="containerName"
        :blueprint="blueprint"
        :meta="meta"
        :errors="errors"
        v-model="values"
    >
        <PublishTabs />
    </PublishContainer>
</template>

<script setup>
import { ref, useTemplateRef } from 'vue';
import {
    PublishContainer,
    PublishTabs,
    Header,
    Button,
} from '@statamic/cms/ui';
import { Pipeline, Request } from '@statamic/cms/save-pipeline';

const emit = defineEmits(['saved']);

const { action, blueprint, meta, values: propValues } = defineProps({
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
});

const containerName = Statamic.$slug.separatedBy('_').create('detour-form');
const container = useTemplateRef('container');
const errors = ref({});
const saving = ref(false);
const values = ref(propValues);
const save = () => {
    new Pipeline()
        .provide({ container, errors, saving })
        .through([new Request(action, 'post')])
        .then((response) => {
            Statamic.$toast.success(__('Saved'));
            emit('saved', response.data);
            values.value = {};
        });
};
</script>