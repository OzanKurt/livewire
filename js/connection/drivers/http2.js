import DOM from '@/dom/dom'

export default {
    onError: null,
    onMessage: null,

    init() {
        //
    },

    getFormData(formData, data, previousKey) {
        if (data instanceof Object) {
            // console.log('data: ' + JSON.stringify(data))
            // console.log('previousKey: ' + previousKey)

            Object.keys(data).forEach(key => {
                const value = data[key];

                console.log('Key: ' + key)
                // if (value instanceof Object) {
                //     console.log('Value: ' + JSON.stringify(value))
                // } else {
                //     console.log('Value: ' + value)
                // }

                if (previousKey) {
                    key = `${previousKey}[${key}]`;
                }

                if (value instanceof Object && !Array.isArray(value)) {
                    // console.log(`Object found, calling recursive getFormData(formData, ${value}, ${key})`)
                    return this.getFormData(formData, value, key);
                }

                if (Array.isArray(value)) {
                    // console.log(`Array found, looping elements...`)

                    if (value.length === 0) {
                        formData.append(`${key}`, []);
                    } else {
                        value.forEach((val, index) => {
                            if (value instanceof Object) {
                                // console.log('Arrays element is an object!')
                                // console.log('Current Key: ' + key)

                                key = key + '[' + index + ']'

                                // console.log('New Key: ' + key)

                                return this.getFormData(formData, val, key);
                            } else {
                                formData.append(`${key}[]`, val);
                            }
                        });
                    }
                } else {
                    if (value == 'null') {
                        formData.append(key, null);
                    } else {
                        formData.append(key, value);
                    }
                    if (value !== null && value.includes('wire\\:file=')) {
                        let el = document.querySelector(`[${value}]`);

                        formData.append(key, el.files[0]);
                    } else {
                    }
                }
            });
        }
    },

    sendMessage(payload) {

        // console.log(payload)

        // const component = document.querySelector(`[wire\\:id="${payload.id}"]`);
        // const allFileElements = DOM.allFileElementsInside(component);

        // console.log(allFileElements)

        // const formData = new FormData();

        // // const fileField = document.querySelector('input[type="file"]');
        // // formData.append('actionQueue[file]', fileField.files[0]);

        // this.getFormData(formData, payload)

        // // Display the key/value pairs
        // for (var pair of formData.entries()) {
        //     console.log(pair[0]+ ' => ' + pair[1]);
        // }

        // const photos = document.querySelector('input[type="file"][multiple]');

        // formData.append('title', 'My Vegas Vacation');
        // for (let i = 0; i < photos.files.length; i++) {
        //   formData.append('photos', photos.files[i]);
        // }

        // Forward the query string for the ajax requests.
        fetch(`${window.livewire_app_url}/livewire/message/${payload.name}${window.location.search}`, {
            method: 'POST',
            body: JSON.stringify(payload),
            // body: formData,
            // This enables "cookies".
            credentials: "same-origin",
            headers: {
                // 'Content-Type': 'multipart/form-data',
                // 'Content-Type': 'application/json',
                'Accept': 'text/html, application/xhtml+xml',
                'X-CSRF-TOKEN': this.getCSRFToken(),
                'X-Socket-ID': this.getSocketId(),
                'X-Livewire': true,
            },
        }).then(response => {
            if (response.ok) {
                response.text().then(response => {
                    if (this.isOutputFromDump(response)) {
                        this.onError(payload)
                        this.showHtmlModal(response)
                    } else {
                        this.onMessage.call(this, JSON.parse(response))
                    }
                })
            } else {
                response.text().then(response => {
                    this.onError(payload)
                    this.showHtmlModal(response)
                })
            }
        }).catch(() => {
            this.onError(payload)
        })
    },

    isOutputFromDump(output) {
        return !! output.match(/<script>Sfdump\(".+"\)<\/script>/)
    },

    getCSRFToken() {
        const tokenTag = document.head.querySelector('meta[name="csrf-token"]')
        let token

        if (!tokenTag) {
            if (!window.livewire_token) {
                throw new Error('Whoops, looks like you haven\'t added a "csrf-token" meta tag')
            }

            token = window.livewire_token
        } else {
            token = tokenTag.content
        }

        return token
    },

    getSocketId() {
        if (typeof Echo !== 'undefined') {
            return Echo.socketId();
        }
    },

    // This code and concept is all Jonathan Reinink - thanks main!
    showHtmlModal(html) {
        let page = document.createElement('html')
        page.innerHTML = html
        page.querySelectorAll('a').forEach(a => a.setAttribute('target', '_top'))

        let modal = document.getElementById('burst-error');

        if(typeof(modal) != 'undefined' && modal != null){
            // Modal already exists.
            modal.innerHTML = ''
        } else {
            modal = document.createElement('div')
            modal.id = 'burst-error'
            modal.style.position = 'fixed'
            modal.style.width = '100vw'
            modal.style.height = '100vh'
            modal.style.padding = '50px'
            modal.style.backgroundColor = 'rgba(0, 0, 0, .6)'
            modal.style.zIndex = 200000
        }

        let iframe = document.createElement('iframe')
        iframe.style.backgroundColor = '#17161A'
        iframe.style.borderRadius = '5px'
        iframe.style.width = '100%'
        iframe.style.height = '100%'
        modal.appendChild(iframe)

        document.body.prepend(modal)
        document.body.style.overflow = 'hidden'
        iframe.contentWindow.document.open()
        iframe.contentWindow.document.write(page.outerHTML)
        iframe.contentWindow.document.close()

        // Close on click.
        modal.addEventListener('click', () => this.hideHtmlModal(modal))

        // Close on escape key press.
        modal.setAttribute('tabindex', 0)
        modal.addEventListener('keydown', (e) => { if (e.key === 'Escape') this.hideHtmlModal(modal) })
        modal.focus()
    },

    hideHtmlModal(modal) {
        modal.outerHTML = ''
        document.body.style.overflow = 'visible'
    },
}
