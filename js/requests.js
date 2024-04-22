class HttpRequest {
    
    GetJSON = async (url) => {
        this._data = await fetch(url);
        this._json = await this._data.json();
        return this._json;
    }
    
    PostJSON = async (url, data) => {
        fetch(url, {
            method: "POST",
            headers: {'Content-Type': 'application/json'}, 
            body: JSON.stringify(data)
          }).then(res => {
            console.log(res);
        });
    };
}