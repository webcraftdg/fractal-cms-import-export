import { ILogger, resolve} from 'aurelia';
import {IValidationRules} from "@aurelia/validation";

import {IImportConfigColumn, ITransformer} from "../interfaces/import-config";

export class Column implements IImportConfigColumn
{
    public id:string;
    public importConfigId:number;
    public source:string;
    public target:string;
    public format:string;
    public default?:any;
    public order:number;
    public transformer?:ITransformer;
    public transformerOptions?:any;

    constructor(
        private readonly logger: ILogger = resolve(ILogger),
        private readonly validationRules: IValidationRules = resolve(IValidationRules)
    ) {
        this.logger = logger.scopeTo('Column');
        this.logger.trace('constructor');
        this.initValidationRules(this.validationRules);
    }


    /**
     * To json
     *
     */
    public toJson()
    {
        return {
            'id': this.id,
            'source': this.source,
            'target': this.target,
            'type': this.format,
            'order': this.order,
            'default': this.default,
            'transformerOptions': this.transformerOptions,
            'transformer': this.transformer.toJson()
        };
    }
    /**
     * Rules validation
     *
     * @param validation
     */
    public initValidationRules(validation: IValidationRules)
    {
        this.logger.trace('initValidationRules');
        validation
            .on(this)
            .ensure('source')
            .required()
            .withMessage('Le source est obligatoire')
            .then()
            .satisfies((value: string, object) => {
                if (!value || value.toString() == '' || typeof(value) !== 'string') {
                    return false;
                }
                return true
            })
            .withMessage('La source doit être une string')
            .ensure('target')
            .required()
            .withMessage('Le target est obligatoire')
            .then()
            .satisfies((value: string, object) => {
                if (!value || value.toString() == '' || typeof(value) !== 'string') {
                    return false;
                }
                return true
            })
            .withMessage('La Cible doit être une string')
            .ensure('type')
            .required()
            .withMessage('Le type est obligatoire')
            .then()
            .satisfies((value: string, object) => {
                if (!value || value.toString() == '' || typeof(value) !== 'string') {
                    return false;
                }
                return true
            })
            .withMessage('Le type doit être une string')
            .ensure('default')
            .then()
            .satisfies((value: string, object) => {
                if (value && typeof(value) !== 'string') {
                    return false;
                }
                return true
            })
            .withMessage('Le default doit être une string')
            .ensure('transformerOptions')
            .required()
            .then()
            .satisfies((value: any, object) => {
                if (value && typeof(value) !== 'string') {
                    return false;
                }
                return true
            })
            .withMessage('Les options sont obligatoires');
    }
}