export enum EApi {
    IMPORT_CONFIG_JSON_GET = '/api/import-config/{id}',
    IMPORT_CONFIG_JSON_GET_COLUMNS = '/api/import-config/{id}/get-columns',
    IMPORT_CONFIG_JSON_POST_COLUMNS = '/api/import-config/{id}/post-columns',
    IMPORT_CONFIG_JSON_DELETE_COLUMN = '/api/import-config/{id}/columns/{columnId}/delete',
    DB_GET_TABLE_COLUMNS = '/api/import-config/{id}/table-columns',
    DB_GET_TABLES = '/api/db/tables',
}