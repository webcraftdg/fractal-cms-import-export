export interface IImportConfigColumn {
    id:string;
    source:string;
    target:string;
    type:string;
    nullable:boolean;
    default?:any;
    transform?:any;
}
export interface IImportConfig {
    id:number;
    name:string;
    version:number;
    active:boolean;
    truncateTable:boolean;
    table:string;
    jsonConfig:string;
    tmpColumns:IImportConfigColumn[];
    dateCreate:string;
    dateUpdate:string;
}