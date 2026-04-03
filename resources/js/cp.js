import DetourSettingsForm from './components/DetourSettingsForm.vue';
import Detours from './components/Detours.vue';

Statamic.booting(() => {
    Statamic.$components.register('Detours', Detours);
    Statamic.$components.register('DetourSettingsForm', DetourSettingsForm);
});
