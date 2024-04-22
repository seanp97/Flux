const GetJSON = async (url) => {
    const _data = await fetch(url);
    const _json = await _data.json();
    return _json;
}

const PostJSON = async (url, data) => {
    fetch(url, {
        method: "POST",
        headers: {'Content-Type': 'application/json'}, 
        body: JSON.stringify(data)
      }).then(res => {
        console.log(res);
    });
};