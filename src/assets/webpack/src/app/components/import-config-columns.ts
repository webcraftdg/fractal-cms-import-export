import {bindable, customElement, IEventAggregator, ILogger, INode, IPlatform, newInstanceForScope,resolve} from 'aurelia';
import {ApiServices} from "../services/api-services";
import {EApi} from "../enums/api";
import {IImportConfig, IImportConfigColumn, IPagination} from "../interfaces/import-config";
import {ConfigService} from "../services/config-service";
import {Column} from "../models/column";
import {IValidationController} from "@aurelia/validation-html";
import {IValidationRules} from "@aurelia/validation";

@customElement('fractal-cms-import-columns')

export class ImportConfigColumns
{
    @bindable public id:string;
    private model:IImportConfig;
    public tmpConfigColumns:IImportConfigColumn[];
    public columns:Column[];
    public tableColumns:IImportConfigColumn[];
    public pagination:IPagination;
    public searchText:any;

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
        const urlTableColums = this.configService.getApiBaseUrl()+EApi.DB_GET_TABLE_COLUMNS.replace('{id}', this.id);

        const getImportConfig = this.apiServices.get(url);
        const getTableColumns = this.apiServices.getTableColumns(urlTableColums);
        Promise.all([
            getImportConfig,
            getTableColumns
        ]).then((result) => {
            this.model = result[0];
            this.tableColumns = result[1];
            const urlGetColmuns = this.prepareGetUrl(this.id);
            this.getColumns(urlGetColmuns);
        });
    }


    public search(event:Event)
    {
        this.logger.trace('search', this.searchText);
        event.preventDefault();
        let urlGetColmuns = this.prepareGetUrl(this.id, null, this.searchText);
        this.getColumns(urlGetColmuns);
    }


    public changePage(event:Event, page:number)
    {
        this.logger.trace('changePage', page);
        this.pagination.page = page;
        event.preventDefault();
        let urlGetColmuns = this.prepareGetUrl(this.id, page, this.searchText);
        this.getColumns(urlGetColmuns);
    }

    private getColumns(url:string) {
        const getColumns = this.apiServices.getColumns(url);
        Promise.all([
            getColumns
        ]).then((result) => {
            this.tmpConfigColumns = result[0][0];
            this.pagination = result[0][1];
            this.loadColumns();
        });
    }

    public move(event:Event, index:number, direction:string)
    {
        this.logger.trace('move', index, direction);
        event.preventDefault();
        const toIndex = (direction == 'up') ? index - 1 : index + 1;
        if (this.columns[index] && this.columns[toIndex]) {
            const sourceOrder = this.columns[index].order;
            this.columns[index].order = this.columns[toIndex].order;
            this.columns[toIndex].order = sourceOrder;
        }
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

    /**
     * Add new column
     *
     * @param event
     */
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

    /**
     * Save
     *
     * @private
     */
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

    /**
     * load column
     * @private
     */
    private loadColumns()
    {
        if (this.tmpConfigColumns) {
            this.columns = [] as Column[];
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

    /**
     * prepare url
     *
     * @param id
     * @param page
     * @param search
     * @private
     */
    private prepareGetUrl(id:string, page:number = null, search:string = null)
    {
        this.logger.trace('prepareGetUrl');
        let url = this.configService.getApiBaseUrl()+EApi.IMPORT_CONFIG_JSON_GET_COLUMNS.replace('{id}', id);
        let params:any = {};
        if (page) {
            params['page'] = page;
        }
        if (search) {
            params['search'] = search;
        }
        const paramsString:string = new URLSearchParams(params).toString();
        if (paramsString) {
            url += '?'+paramsString;
        }
        return url;
    }

}

