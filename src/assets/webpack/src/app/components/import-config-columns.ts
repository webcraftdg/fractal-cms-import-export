import {bindable, customElement, IEventAggregator, ILogger, INode, IPlatform, newInstanceForScope,resolve} from 'aurelia';
import {ApiServices} from "../services/api-services";
import {EApi} from "../enums/api";
import {IImportConfig, IImportConfigColumn, IPagination} from "../interfaces/import-config";
import {ConfigService} from "../services/config-service";
import {Column} from "../models/column";
import {IValidationController} from "@aurelia/validation-html";
import {IValidationRules} from "@aurelia/validation";
import {data} from "autoprefixer";

@customElement('fractal-cms-import-columns')

export class ImportConfigColumns
{
    @bindable public id:string;
    private model:IImportConfig;
    public tmpConfigColumns:IImportConfigColumn[];
    public columns:Column[];
    public tableColumns:IImportConfigColumn[];
    public pagination:IPagination;

    constructor(
        private readonly logger: ILogger = resolve(ILogger),
        private readonly ea: IEventAggregator = resolve(IEventAggregator),
        private readonly platform: IPlatform = resolve(IPlatform),
        private readonly element: HTMLElement = resolve(INode) as HTMLElement,
        private readonly validationController:IValidationController = resolve(newInstanceForScope(IValidationController)),
        private readonly validationRules:IValidationRules = resolve(IValidationRules),
        private readonly configService:ConfigService = resolve(ConfigService),
        private readonly apiServices: ApiServices = resolve(ApiServices)
    ) {
        this.logger = logger.scopeTo('ImportConfigColumns');
        this.logger.trace('constructor');
        this.model = {} as IImportConfig;
        this.columns = [] as Column[];
        this.tmpConfigColumns = [] as Column[];
        this.tableColumns = [] as IImportConfigColumn[];
        this.pagination = {} as IPagination;
    }

    public binding() {
        this.logger.trace('binding', this.id);
    }

    public attached() {
        this.logger.trace('attached', this.id);
        const url = this.configService.getApiBaseUrl()+EApi.IMPORT_CONFIG_JSON_GET.replace('{id}', this.id);
        const urlGetColmuns = this.configService.getApiBaseUrl()+EApi.IMPORT_CONFIG_JSON_GET_COLUMNS.replace('{id}', this.id);
        const urlTableColums = this.configService.getApiBaseUrl()+EApi.DB_GET_TABLE_COLUMNS.replace('{id}', this.id);

        const getImportConfig = this.apiServices.get(url);
        const getColumns = this.apiServices.getColumns(urlGetColmuns);
        const getTableColumns = this.apiServices.getTableColumns(urlTableColums);
        Promise.all([
            getImportConfig,
            getColumns,
            getTableColumns
        ]).then((result) => {
            this.model = result[0];
            this.tmpConfigColumns = result[1];
            this.tableColumns = result[2];

            this.loadColumns();
        });
    }

    /**
     * load column
     * @private
     */
    private loadColumns()
    {
        if (this.tmpConfigColumns) {
            this.logger.trace('loadColumns', this.tmpConfigColumns);
            this.tmpConfigColumns.forEach((value:IImportConfigColumn, index)=> {
                let newColumn:IImportConfigColumn = Object.assign(value, {} as IImportConfigColumn);
                const columnModel = new Column(this.logger, this.validationRules);
                Object.assign(columnModel, newColumn);
                this.validationController.addObject(columnModel);
                this.columns.push(columnModel);
            });
        }
    }

    public move(event:Event, index:number, direction:string)
    {
        this.logger.trace('move', index, direction);
        event.preventDefault();
        const toIndex = (direction == 'up') ? index - 1 : index + 1;
        const item = this.columns.splice(index, 1);
        this.columns.splice(toIndex, 0, item[0]);
        this.platform.taskQueue.queueTask(() => {
            this.save();
        }, {delay:50});
    }

    /**
     * Delete
     *
     * @param event
     * @param columnId
     * @param index
     */
    public delete(event:Event, columnId:string, index:number)
    {
        this.logger.trace('delete',  columnId);
        event.preventDefault();
        if (columnId) {
            let url = this.configService.getApiBaseUrl()+EApi.IMPORT_CONFIG_JSON_DELETE_COLUMN.replace('{id}', this.id);
            url = url.replace('{columnId}', columnId);
            const deleteColumn = this.apiServices.delete(url);
            Promise.all([
                deleteColumn
            ]).then((result) => {
                this.logger.trace('delete : OK',  columnId);
            });
        }
        this.remove(index);
    }

    /**
     * remove form array
     *
     * @param index
     * @private
     */
    private remove(index:number)
    {
        this.logger.trace('remove');
        const deleted  = this.columns.splice(index, 1);
        this.validationController.removeObject(deleted[0]);
    }

    public add(event:Event)
    {
        this.logger.trace('add');
        event.preventDefault();
        const newColumn = {} as IImportConfigColumn;
        const newModel = new Column(this.logger, this.validationRules);
        Object.assign(newModel, newColumn);
        this.validationController.addObject(newModel);
        this.columns.push(newModel);
        this.platform.taskQueue.queueTask(() => {
            this.save();
        }, {delay:50});
    }

    private save()
    {
        this.logger.trace('save');
        this.validationController.validate().then((validation) => {
            if (validation.valid) {
                this.logger.trace('save: success');
                const url = this.configService.getApiBaseUrl()+EApi.IMPORT_CONFIG_JSON_POST_COLUMNS.replace('{id}', this.id);
                const data = this.columns.map((value:Column, index) => {
                    return value.toJson();
                });
                return this.apiServices.post(url, data)
                    .then((result) => {
                        this.logger.trace('save');
                    });
            } else {
                this.logger.trace('onSubmit:error', validation.results);
            }

        }).catch((error) => {
            this.logger.trace('save:error', error);
        });
    }

}

