import Detours from './components/Detours.vue';

Statamic.booting(() => {
    Statamic.$components.register('detours', Detours);
});
