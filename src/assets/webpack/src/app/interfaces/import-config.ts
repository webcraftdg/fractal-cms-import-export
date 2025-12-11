export interface IImportConfigColumn {
    id:string;
    importConfigId:number;
    source:string;
    target:string;
    type:string;
    defaultValue?:any;
    transformer?:ITransformer;
    transformerOptions?:any;
    order:number;
}
export interface IImportConfig {
    id:number;
    name:string;
    version:number;
    active:boolean;
    truncateTable:boolean;
    table:string;
    jsonConfig:string;
    dateCreate:string;
    dateUpdate:string;
    pagination:IPagination;
}
export interface IPagination {
    page: number;
    pageSize: number;
    totalCount?: number;
    pageCount?: number;
}
export interface IHttpResponse {
    status:any;
    statusText:any;
    headers:any;
    data:any;
    extras:any;
}
export interface ITransformer {
    name:string;
    description:string;
    optionsSchema?:IOptionSchema[];
    toJson():any;
}
export interface IOptionSchema {
    key:string;
    type:string;
    required:boolean;
    label:string;
    value?:any;
}