import { Autocomplete } from "./Autocomplete";
import type { AutocompleteOptions } from "./types";
declare global {
    interface JQueryStatic {
        Autocomplete: typeof Autocomplete;
    }
    interface JQuery {
        autocomplete(options?: AutocompleteOptions | string, args?: unknown): JQuery | Autocomplete;
        devbridgeAutocomplete(options?: AutocompleteOptions | string, args?: unknown): JQuery | Autocomplete;
    }
}
export declare function installAutocomplete($: JQueryStatic): void;
