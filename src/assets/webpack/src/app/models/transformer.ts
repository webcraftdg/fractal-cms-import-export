import { ILogger, resolve} from 'aurelia';
import {IValidationRules} from "@aurelia/validation";

import {IOptionSchema, ITransformer} from "../interfaces/import-config";

export class Transformer implements ITransformer
{
    public name:string;
    public description:string;
    public optionsSchema?:IOptionSchema[];

    constructor(
        private readonly logger: ILogger = resolve(ILogger),
        private readonly validationRules: IValidationRules = resolve(IValidationRules)
    ) {
        this.logger = logger.scopeTo('Transformer');
        this.logger.trace('constructor');
        this.initValidationRules(this.validationRules);
        this.optionsSchema = [] as IOptionSchema[];
    }

    /**
     * To json
     *
     */
    public toJson()
    {
        return {
            'name': this.name,
            'description': this.description,
            'optionsSchema': this.optionsSchema,
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
            .ensure('name')
            .required()
            .withMessage('Le name est obligatoire')
            .then()
            .satisfies((value: string, object) => {
                if (!value || value.toString() == '' || typeof(value) !== 'string') {
                    return false;
                }
                return true
            });
        if (this.optionsSchema) {
            for (const option in this.optionsSchema) {
                if (this.optionsSchema[option].required) {
                    validation.on(this.optionsSchema[option])
                        .ensure(option)
                        .required()
                        .withMessage('Le '+this.optionsSchema[option].key+' est obligatoire')
                        .then()
                        .satisfies((value: string, object) => {
                            if (!value || value.toString() == '' || typeof(value) !== 'string') {
                                return false;
                            }
                            return true
                        });
                }
            }
        }
    }
}