import Action from '.'

export default class extends Action {
    constructor(name, el) {
        super(el)

        this.type = 'fileChanged'
        const value = name.includes('[]') ? el.el.files : el.el.files[0];

        this.payload = {
            name,
            value,
        }
    }
}
