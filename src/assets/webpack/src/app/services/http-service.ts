import { HttpClientConfiguration, IHttpClient } from '@aurelia/fetch-client';
import {DI, ILogger, resolve} from 'aurelia';
import {ConfigService} from "./config-service";
import {IHttpResponse, IPagination} from "../interfaces/import-config";

export const IHttpService =
    DI.createInterface<IHttpService>('IHttpService', (x) =>
        x.singleton(HttpService)
    );
export interface IHttpService extends HttpService {}
export class HttpService {

    public constructor(
        private readonly httpClient: IHttpClient = resolve(IHttpClient),
        private readonly configService:ConfigService = resolve(ConfigService),
        private readonly logger: ILogger = resolve(ILogger).scopeTo('HttpService')
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
     * @param requestInit
     */
    public delete(url: string, requestInit: RequestInit|null = null): Promise<IHttpResponse>
    {
        if (requestInit == null) {
            requestInit = {
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'text/plain'
                }
            };
        }
        requestInit.method = 'DELETE';
        return this.fetch(url, requestInit, null);
    }


    /**
     *
     * @param url
     * @param requestConfig
     * @param data
     */
    public getJson(url:string, requestConfig:any = null, data:any = null)
    {
        if (requestConfig === null) {
            requestConfig = {
                headers:{
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            };
        }
        if(!requestConfig.method) {
            requestConfig.method = 'GET';
        }
        return this.fetch(url, requestConfig, data);
    }

    /**
     * fetch Json
     *
     * @param url
     * @param requestConfig
     * @param data
     */
    public fetchJson(url:string, requestConfig:any = null, data:any = null)
    {
        if (requestConfig === null) {
            requestConfig = {
                headers:{
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            };
        }
        if(!requestConfig.method) {
            requestConfig.method = 'POST';
        }
        return this.fetch(url, requestConfig, data);
    }

    /**
     * fetch
     *
     * @param url
     * @param requestConfig
     * @param data
     */
    public fetch(url:string, requestConfig:any = null, data:any = null)
    {
        if (requestConfig == null) {
            requestConfig = {
                headers: {
                    'Accept': '*/*'
                }
            };
        }
        if (data != null) {
            requestConfig.body = JSON.stringify(data);
        }

        return this.httpClient.fetch(url, requestConfig)
            .then((response:Response) => {
                const headers = response.headers;
                const status = response.status;
                const statusText = response.statusText;
                const contentType:string|null = headers.get('Content-Type');
                let data:any =  response;
                if (contentType.indexOf('json') !== -1) {
                    data = response.json();
                } else if (contentType.indexOf('text') !== -1) {
                    data = response.text();
                } else {
                    data = response.blob();
                }
                return Promise.all([status, statusText, headers, data]);
            }).then(([status, statusText, headers, data])=> {
                const response:IHttpResponse = {
                    status,
                    statusText,
                    headers,
                    data,
                    extras: {}
                };
                response.extras = this.buildExtrasHeaders(headers);
                return response;
            });
    }

    /**
     *
     * @param headers
     * @private
     */
    private buildExtrasHeaders(headers:Headers):any {
        const extras = {
            pagination: {} as IPagination
        };
        const pagination:IPagination = {
            page: -1,
            pageCount: -1,
            pageSize: -1,
            totalCount: -1
        };
        headers.forEach((value, key) => {
            const lowerKey = key.trim().toLowerCase();
            switch (lowerKey) {
                case 'x-pagination-current-page':
                    pagination.page = Number.parseInt(value);
                    break;
                case 'x-pagination-total-page':
                    pagination.pageCount = Number.parseInt(value);
                    break;
                case 'x-pagination-per-page':
                    pagination.pageSize = Number.parseInt(value);
                    break;
                case 'x-pagination-total-entries':
                    pagination.totalCount = Number.parseInt(value);
                    break;
            }
        });
        if (pagination.page > -1 && pagination.pageCount > -1 && pagination.pageSize > -1 && pagination.totalCount > -1) {
            extras.pagination = pagination;
        }
        return extras;
    }
}
