import { initializeApp } from "firebase/app";
import { getDatabase, ref, onValue } from "firebase/database";

const firebaseConfig = {
    apiKey: "AIzaSyADMNl_LobwjBdkWFhPJ7Mp_EMgEXc3jHY",
    authDomain: "simlab-ith.firebaseapp.com",
    databaseURL: "https://simlab-ith-default-rtdb.firebaseio.com/",
    projectId: "simlab-ith",
};

const app = initializeApp(firebaseConfig);
const db = getDatabase(app);

const fingerprintRef = ref(db, 'fingerprint');

window.addEventListener('livewire:init', () => {
    onValue(fingerprintRef, (snapshot) => {
        const data = snapshot.val();

        window.Livewire.dispatch('fingerprint-scanned', {
            data: data
        });
    });
});

