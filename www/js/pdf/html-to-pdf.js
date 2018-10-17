/**
 * Created by kostet on 17.10.2018.
 */


var fs = require('fs');
var pdf = require('html-pdf');
var webshot = require('webshot');

var html = fs.readFileSync('/var/www/vwgroup/data/www/dm.vw-servicepool.ru/www/js/pdf/activity_consolidated_information.html', 'utf8');
var options = {
    format: 'Letter',
    base: 'file:///var/www/vwgroup/data/www/dm.vw-servicepool.ru/www/js/pdf/',
    quality: 100
};

pdf.create(html, options).toFile('activity_consolidated_information.pdf', function(err, res) {
    console.log(err);
});


/*webshot(html, 'test.png', { siteType: 'html'}, function(erro) {
    console.log(err)
});*/
