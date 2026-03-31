import {bindable, customElement, ILogger, INode,resolve} from 'aurelia';
import {ApiServices} from "../services/api-services";
import {IImportConfigColumn, ITransformer} from "../interfaces/import-config";
import {ConfigService} from "../services/config-service";
import {EApi} from "../enums/api";

@customElement('fractal-cms-import-column')

export class Column
{
    @bindable() public model:IImportConfigColumn;
    @bindable() public importConfigId:string;
    @bindable() public parent:any;
    @bindable() public index:number;
    @bindable() public type:string;
    @bindable() public tableColumns:IImportConfigColumn[];
    @bindable() public transformers:ITransformer[];

    private inputSelect:HTMLSelectElement;

    public searchText:string;
    constructor(
        private readonly logger: ILogger = resolve(ILogger),
        private readonly element: HTMLElement = resolve(INode) as HTMLElement,
        private readonly configService:ConfigService = resolve(ConfigService),
        private readonly apiServices: ApiServices = resolve(ApiServices)
    ) {
        this.logger = logger.scopeTo('ImportConfigColumns');
        this.logger.trace('constructor');
        this.searchText = null;
        this.model = {} as IImportConfigColumn;
        this.tableColumns = [] as IImportConfigColumn[];
        this.transformers = [] as ITransformer[];
    }

    public binding() {
        this.logger.trace('binding', this.model);
    }

    public attached() {
        this.logger.trace('attached', this.model);
        this.inputSelect = this.element.querySelector('.columns');
        if (this.inputSelect) {
            this.inputSelect.style.display = 'none';
        }
    }

    transformerMatcher = (a:ITransformer, b:ITransformer) =>
    {
        return ((a && typeof(a)== 'object' && a.hasOwnProperty('name')) && (b && typeof(b)== 'object' && b.hasOwnProperty('name')) && a.name == b.name);
    }

    /**
     * 
     * @param event 
     * @param active 
     * @returns 
     */
    public onInputChange(event:Event, active:boolean = false)
    {
        this.logger.trace('onInputChange');
        event.preventDefault();
        if (active === true) {
            const value = (this.type === 'import') ? this.model.target : this.model.source;
            return this.prepareTableColumnNames(value);
        } else {
            this.tableColumns = [];
        }
    }


    /**
     * 
     * @param value 
     * @returns 
     */
    public prepareTableColumnNames(value?:string)
    {
        this.logger.trace('prepareTableColumnNames');
        let urlTableColumns = this.configService.getApiBaseUrl()+EApi.DB_GET_TABLE_COLUMNS.replace('{id}', this.importConfigId);
        if (value) {
            urlTableColumns = this.configService.getApiBaseUrl()+EApi.DB_GET_TABLE_COLUMNS_BY_NAME.replace('{id}', this.importConfigId).replace('{name}', value);
        }
        const getTableColumns = this.apiServices.getTableColumns(urlTableColumns);
        return Promise.all([
            getTableColumns
        ]).then((result) => {
            this.tableColumns = result[0];
            if (this.inputSelect && this.tableColumns.length > 0) {
                this.inputSelect.style.display = 'block';
            }
        });
    }

    /**
     *
     * @param event
     */
    public changeSelect(event:Event)
    {
        this.logger.trace('changeSelect');
        event.preventDefault();
        if (this.inputSelect) {
            this.inputSelect.querySelectorAll('option').forEach((option, key) => {
                if (option.value) {
                    if (option.selected) {
                        if (this.type === 'import') {
                            this.model.target = option.value;
                        } else {
                            this.model.source = option.value;
                        }
                    }
                }
            });
        }
    }

}

