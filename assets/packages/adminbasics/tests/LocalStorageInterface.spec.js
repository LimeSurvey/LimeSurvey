import 'jest-localstorage-mock';
import LocalStorageInterface from '../src/components/LocalStorageInterface.js';
import isEmpty from 'lodash/isEmpty';
import merge from 'lodash/merge';

describe("Basic functionality", () => {

    beforeEach(() => {
        global.LS = {localStorageInterface: new LocalStorageInterface()};
    });

    test("Empty storage has been created", () => {
        expect( 
            isEmpty(global.LS.localStorageInterface.stateNames)
            && isEmpty(global.LS.localStorageInterface.archive)
        ).toBeTruthy();
    });

    test("LocalStorage has been called once", () => {
        expect(global.localStorage.getItem).toHaveBeenLastCalledWith('LsPersistentStorageArchive');
    });

    test("LocalStorage has been written with the empty archive", () => {
        expect(global.localStorage.setItem).toHaveBeenLastCalledWith('LsPersistentStorageArchive', '{}');
    });

});

describe("Creating a save state", () => {
    let creationTime, saveState;
    beforeAll(() => {
        global.LS = {localStorageInterface: new LocalStorageInterface()};
        saveState = global.LS.localStorageInterface.createSaveState('TESTSTATE');
        saveState('TESTSTATE', { valueStored: 'TESTVALUE' }, global.localStorage);
        creationTime = Math.floor(new Date().getTime()/100);
    });
    
    /*
    test("A safe state has been created", () => {
        expect( 
            Math.floor(global.LS.localStorageInterface.archive['TESTSTATE'].created/100)
        ).toBe( creationTime);
    });
    */

    test("The stored archive has been updated", () => {
        expect(global.localStorage.setItem).toHaveBeenCalled();
    });
    
    test("The values where stored correctly", () => {
        const localStorageValues = JSON.parse(global.localStorage.getItem('TESTSTATE'));
        expect(localStorageValues).toStrictEqual({valueStored:'TESTVALUE'});
    });

    /*
    test("Timestamp on state has been updated", () => {
        const dateBefore = Math.floor(global.LS.localStorageInterface.archive.TESTSTATE.created/100);
        saveState('TESTSTATE', { valueStored: 'TESTVALUE' }, global.localStorage);
        expect(dateBefore).toBe(Math.floor(global.LS.localStorageInterface.archive.TESTSTATE.created/100));
    });
    */

});

describe("Checking that old and faulty values are removed", () => {
    let saveState;
    const expiredTimestamp = (new Date().getTime() - (1000*60*60*2));

    beforeEach(() => {
        global.LS = {localStorageInterface: new LocalStorageInterface()};
        saveState = global.LS.localStorageInterface.createSaveState('TESTSTATE');
        saveState('TESTSTATE', { valueStored: 'TESTVALUE' }, global.localStorage);
        global.LS.localStorageInterface.archive['TESTSTATE'].created = expiredTimestamp;
        window.localStorage.setItem('LsPersistentStorageArchive', JSON.stringify(global.LS.localStorageInterface.archive));
    });

    test("An old value has been set", () => {
        const storedArchive = window.localStorage.getItem('LsPersistentStorageArchive');
        const parsedStoredArchive = JSON.parse(storedArchive);
        expect(parsedStoredArchive).toStrictEqual({ TESTSTATE: { created: expiredTimestamp } });
    });

    test("The old value has been removed", () => {
        global.LS.localStorageInterface.refreshArchive();
        const storedArchive = window.localStorage.getItem('LsPersistentStorageArchive');
        const parsedStoredArchive = JSON.parse(storedArchive);
        expect(parsedStoredArchive).toStrictEqual({});
    });

    test("A faulty value will be removed", () => {
        const faultyArchive = merge(global.LS.localStorageInterface.archive, {'FAULTYSTATE' : {created: 123456789}});
        window.localStorage.setItem('LsPersistentStorageArchive', JSON.stringify(faultyArchive));
        global.LS.localStorageInterface.refreshArchive();
        const storedArchive = window.localStorage.getItem('LsPersistentStorageArchive');
        const parsedStoredArchive = JSON.parse(storedArchive);
        expect(parsedStoredArchive).toStrictEqual({});
    });

});
