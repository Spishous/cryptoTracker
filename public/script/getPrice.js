var currentQuote=1,currentId=0;

async function getLatestQuote(currentId2=currentId){
    return new Promise(function (resolve, reject) {
        let xhr = new XMLHttpRequest();
        xhr.open("GET", "https://pro-api.coinmarketcap.com/v1/cryptocurrency/quotes/latest?id="+currentId2);
        xhr.setRequestHeader("X-CMC_PRO_API_KEY", "b3163f42-6606-4471-8647-da8d1adb922d");

        xhr.onload = function () {
            if (this.status >= 200 && this.status < 300) {
                resolve(JSON.parse(xhr.response)['data'][currentId2]['quote']['USD']['price']);
            } else {
                reject({
                    status: this.status,
                    statusText: xhr.statusText
                });
            }
        };
        xhr.onerror = function () {
            reject({
                status: this.status,
                statusText: xhr.statusText
            });
        };
        xhr.send();
    })
}
function get2Decimal(float){
    let result=parseFloat(float.toString().slice(0,float.toString().indexOf(".")+3));
    if(isNaN(result)) result=0;
    return result
}
