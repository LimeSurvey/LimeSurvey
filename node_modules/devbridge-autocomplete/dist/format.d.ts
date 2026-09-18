import type { AutocompleteResponse, Suggestion } from "./types";
export declare function lookupFilter(suggestion: Suggestion, _originalQuery: string, queryLowerCase: string): boolean;
export declare function transformResult(response: string | AutocompleteResponse): AutocompleteResponse;
export declare function formatResult(suggestion: Suggestion, currentValue: string): string;
export declare function formatGroup(_suggestion: Suggestion, category: string): string;
