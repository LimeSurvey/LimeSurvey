import filter from 'lodash/filter';
import pickBy from 'lodash/pickBy';
import merge from 'lodash/merge';
import uniq from 'lodash/uniq';
import keys from 'lodash/keys';
import empty from 'lodash/isEmpty';

/**
 * Creates an interface class to winodow.localStorage, that we can extend
 * This got necessary due to a constant overload and overall downspiraling performance
 * 
 */
export default class LocalStorageInterface {
    /**
     * Create the Interface object to create an archive of the registered 
     */
    constructor() {
        this.stateNames = [];
        this.archive = {};
        this.refreshArchive();
    }

    /**
     * This is a different way to filter and control the stored localStorage keys
     * @TODO make this possible
     * 
     * @param String name -> the name of the mapped state
     */
    registerStateName(name) {
        const tmpArray = merge([], this.stateNames);
        tmpArray.push(name);
        this.stateNames = uniq(tmpArray);
    }

    /**
     * Returns the saveState function used for state persistence
     * It will trigger a cleanup on the already stored keys
     *
     * @param String name  -> the name used for storing the state in localStorage
     *
     * @returns function The saveState function used for state persistence
     */
    getSaveState(name) {
        this.archive[name] = {
            name: name,
            created: new Date().getTime()
        }

        this.refreshArchive();
        return this.createSaveState(name);
    }
    
    /**
     * Cleanup and refresh method.
     * Currently checking the keys for last usage.
     * A key last used more than an hour ago, will be deleted from localStorage
     */
    refreshArchive() {
        const currentTime = new Date().getTime();
        const ttl = currentTime - (1000*60*60); // 1 hour

        let currentStoredArchive = window.localStorage.getItem('LsPersistentStorageArchive');
        try {
            currentStoredArchive = JSON.parse(currentStoredArchive);
        } catch(e) {}

        if(empty(currentStoredArchive)) {
            currentStoredArchive = this.archive;
        } else {            
            currentStoredArchive = merge(currentStoredArchive, this.archive);
        }

        const toBeRemoved = filter(currentStoredArchive, (entry) => (entry.created < ttl) );
        this.archive = pickBy(currentStoredArchive, (entry) => (entry.created >= ttl) );
        
        window.localStorage.setItem('LsPersistentStorageArchive', JSON.stringify(this.archive));

        this.purgeInvalidated(toBeRemoved);
        this.removeDeadReferences();
    }

    /**
     * Method to remove keys from localStorage
     * 
     * @param Array toBeRemoved  keys that will be removed from the localStorage
     */
    purgeInvalidated(toBeRemoved) {
        toBeRemoved.forEach((toBeRemovedItem) => {
            window.localStorage.removeItem(toBeRemovedItem.name);
        })
    }
    
    /**
     * Filters the currently stored keys in localStorage
     * and removes them, if they are not stored in the archive map.
     */
    removeDeadReferences() {
        for (let i=0; i< localStorage.length; i++) {
            const key = localStorage.key( i );
            if(key === 'LsPersistentStorageArchive') { continue; }
            if(keys(this.archive).indexOf(key) < 0) {
                window.localStorage.removeItem(key);
            }
        }
    }

    updateArchiveTimestamp(name) {
        this.archive[name] = this.archive[name] || {};
        this.archive[name].created = new Date().getTime();
        this.refreshArchive();
    }

    /**
     * SaveState method for state persistence
     *
     * @param String name The name of the state to be created
     *
     * @return λ-function with definition (key, state, storage)
     */
    createSaveState(name) {
        const saveState = function(key, state, storage) {
            LS.localStorageInterface.updateArchiveTimestamp(name);
            storage.setItem(
                key,
                JSON.stringify(state)
            );
        }

        return saveState;

    }

    /**
     * Returns the window localStorage
     * @TODO create alternatives
     */
    getLocalStorage() {
        return window.localStorage;
    }

}

