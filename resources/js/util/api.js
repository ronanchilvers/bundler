class Api {
    constructor() {
    }

    async loadBoard(id) {
        return this.request('GET', `/board/${id}`);
    }

    async createColumn(boardId, column) {
        console.log('api.createColumn');
        return this.request('POST', `/board/${boardId}/column`, column);
    }

    async deleteColumn(boardId, columnId) {
        return this.request('DELETE', `/board/${boardId}/column/${columnId}`);
    }

    async createCard(boardId, columnId, card) {
        return this.request('POST', `/board/${boardId}/column/${columnId}/card`, card);
    }

    async deleteCard(boardId, columnId, cardId) {
        return this.request('DELETE', `/board/${boardId}/column/${columnId}/card/${cardId}`);
    }

    async request(method, path, data = null) {
        const options = {
            method,
            headers: {
                'Content-Type': 'application/json',
                // Add other common headers here
            }
        };

        if (data !== null) {
            options.body = JSON.stringify(data);
        }

        const response = await fetch(`/api${path}`, options);
        return this.handleResponse(response);
    }

    async handleResponse(response) {
        if (!response.ok) {
            const message = await response.text();
            this.handleError(new Error(`HTTP ${response.status}: ${message}`));
        }
        return response.json();
    }

    handleError(error) {
        console.error('API error:', error);
        throw error;
    }
}

export default Api;
