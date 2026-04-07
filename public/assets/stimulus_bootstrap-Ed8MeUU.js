import { startStimulusApp } from '@symfony/stimulus-bundle';
import  LiveController  from '@symfony/ux-live-component';

const app = startStimulusApp();
// register any custom, 3rd party controllers here
// app.register('some_controller_name', SomeImportedController);
app.register('live', LiveController);
