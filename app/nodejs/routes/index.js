
/*
 * GET home page.
 */

exports.index = function(req, res){
  res.render('index', { title: 'Poker', content: '<b>TEST</b>' });
};