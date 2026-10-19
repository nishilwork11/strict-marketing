// Changes the text of a button's roll label (x-roll-label), and of the copy that rolls in under it
export function setRollLabel(button, text) {
    for (const part of button.querySelectorAll(':scope > .roll > span')) {
        part.textContent = text;
    }
}
