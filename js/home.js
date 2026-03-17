import { ModPlayer } from './player.js';
const audio = new AudioContext();
const player = new ModPlayer(audio);
await player.load('/mod/FURY-INTRO.MOD');
window.addEventListener('click', async () => {
    player.play();
});