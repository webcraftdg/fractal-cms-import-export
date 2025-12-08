export interface IImportConfigColumn {
    id:string;
    importConfigId:number;
    source:string;
    target:string;
    type:string;
    defaultValue?:any;
    transform?:any;
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