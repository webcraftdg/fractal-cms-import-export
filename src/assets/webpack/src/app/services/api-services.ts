import { HttpClientConfiguration, IHttpClient } from '@aurelia/fetch-client';
import {ILogger, resolve} from 'aurelia';
import {ConfigService} from "./config-service";
import {IDbColumn, IImportConfig, IImportConfigColumn} from "../interfaces/import-config";
import {Column} from "../models/column";

export class ApiServices {

    public constructor(
        private readonly httpClient: IHttpClient = resolve(IHttpClient),
        private readonly configService:ConfigService = resolve(ConfigService),
        private readonly logger: ILogger = resolve(ILogger).scopeTo('ApiServices')
    )
    {
        this.logger.trace('constructor');
        this.httpClient.configure((config: HttpClientConfiguration) => {
            config.withDefaults({
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'include'
            }).withInterceptor({
                request(request) {
                    console.log(`Requesting ${request.method} ${request.url}`);
                    return request;
                },
                response(response) {
                    console.log(`Received ${response.status} ${response.url}`);
                    return response;
                },
            }).rejectErrorResponses();
            return config;
        });

    }


    /**
     *
     * @param url
     * @param data
     */
    public post(url:string, data:any[]): Promise<IImportConfigColumn[]>
    {

        return this.httpClient.fetch(url, {
            method: 'POST',
            body:JSON.stringify(data),
            headers:{
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        })
            .then((response:Response) => {
                return response.json();
            }).then((result:IImportConfigColumn[])=> {
                return result;
            });
    }

    /**
     * Get tables
     *
     * @param url
     */
    public getTableColumns(url:string): Promise<IDbColumn[]>
    {
        return this.httpClient.fetch(url, {
            method: 'GET',
            headers:{
                'Accept': 'application/json',
            }
        })
            .then((response:Response) => {
                return response.json();
            }).then((result:IDbColumn[])=> {
                return result;
            });
    }

    public get(url:string): Promise<IImportConfig>
    {
        return this.httpClient.fetch(url, {
            method: 'GET',
            headers:{
                'Accept': 'application/json',
            }
        })
            .then((response:Response) => {
                return response.json();
            }).then((result:IImportConfig)=> {
                return result;
            });
    }
}
