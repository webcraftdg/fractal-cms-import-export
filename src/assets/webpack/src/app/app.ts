import {ILogger, resolve} from 'aurelia';
export class ImportExportApp {
    constructor(
        private readonly logger: ILogger = resolve(ILogger),
    ) {
        this.logger = logger.scopeTo('ImportExportApp');

    }

    public binding() {
        this.logger.trace('binding');
    }

    public attaching()
    {
        this.logger.trace('Attaching');
    }
}