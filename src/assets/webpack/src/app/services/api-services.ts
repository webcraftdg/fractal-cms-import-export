import { HttpClientConfiguration, IHttpClient } from '@aurelia/fetch-client';
import {ILogger, resolve} from 'aurelia';
import {ConfigService} from "./config-service";
import {
    IHttpResponse,
    IImportConfig,
    IImportConfigColumn,
    IPagination,
    ITransformer
} from "../interfaces/import-config";
import {IHttpService} from "./http-service";

export class ApiServices {

    public constructor(
        private readonly httpClient: IHttpClient = resolve(IHttpClient),
        private readonly configService:ConfigService = resolve(ConfigService),
        private readonly httpService: IHttpService = resolve(IHttpService),
        private readonly logger: ILogger = resolve(ILogger).scopeTo('ApiServices')
    )
    {
        this.logger.trace('constructor');
    }

    /**
     *
     * @param url
     */
    public get(url:string): Promise<IImportConfig>
    {
        return this.httpService.getJson(url, null, null).then((result) => {
           return result.data as IImportConfig;
        });
    }

    /**
     *
     * @param url
     * @param data
     */
    public post(url:string, data:any[]): Promise<IImportConfigColumn[]>
    {
        return this.httpService.fetchJson(url, null, data).then((result) => {
            return result.data as IImportConfigColumn[];
        });
    }

    public delete(url:string): Promise<string>
    {
        return this.httpService.delete(url, null).then((result) => {
            return result.data;
        });
    }


    /**
     *
     * @param url
     */
    public getColumns(url:string): Promise<[IImportConfigColumn[], IPagination]>
    {
        return this.httpService.getJson(url, null, null).then((result) => {
            return [result.data as IImportConfigColumn[], result.extras?.pagination];
        });
    }

    /**
     *
     * @param url
     */
    public getTransformer(url:string): Promise<ITransformer[]>
    {
        return this.httpService.getJson(url, null, null).then((result) => {
            return result.data as ITransformer[];
        });
    }

    /**
     *
     * @param url
     * @param data
     */
    public postColumns(url:string, data:any[]): Promise<IImportConfigColumn[]>
    {
        return this.httpService.fetchJson(url, null, data).then((result) => {
            return result.data as IImportConfigColumn[];
        });
    }

    /**
     * Get tables
     *
     * @param url
     */
    public getTableColumns(url:string): Promise<IImportConfigColumn[]>
    {

        return this.httpService.getJson(url, null, null).then((result) => {
            return result.data as IImportConfigColumn[];
        });
    }
}
