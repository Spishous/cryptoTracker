var currentQuote=1,currentId=0,url="/crypto-api/";
aze=document.querySelector('.qsdqsd');
function getLatestQuote(currentId2=currentId,callback){
        let xhr = new XMLHttpRequest();
        xhr.open("GET", url+currentId2);
        xhr.onload = ()=>{
            let result=JSON.parse(xhr.response)[currentId2]['quote']['USD']['price'];
            callback(result);
        }
        xhr.send();
}
function getToDecimal(float){
    let result=parseFloat(float.toString().slice(0,float.toString().indexOf(".")+3));
    if(isNaN(result)) result=0;
    return result
}
