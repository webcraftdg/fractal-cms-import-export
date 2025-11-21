//Aurelia imports
import Aurelia, { ConsoleSink, LoggerConfiguration, LogLevel } from 'aurelia';
// Plugins imports
// app imports
import {ImportExportApp} from "./app/app";

declare const webpackBaseUrl: string;
declare let __webpack_public_path__: string;
declare let apiBaseUrl: string;
if (webpackBaseUrl !== undefined) {
    __webpack_public_path__ = webpackBaseUrl;
}
declare const PRODUCTION:boolean;


const page = document.querySelector('body') as HTMLElement;
const au = Aurelia;

if(PRODUCTION == false) {
    au.register(LoggerConfiguration.create({
        level: LogLevel.trace,
        colorOptions: 'colors',
        sinks: [ConsoleSink]
    }));

}
au.enhance({
    host: page,
    component: ImportExportApp
});
