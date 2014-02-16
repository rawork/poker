var MongoClient = require('mongodb').MongoClient
    , format = require('util').format;

MongoClient.connect('mongodb://127.0.0.1:27017/holdem', function(err, db) {
    if(err) throw err;

    var collection = db.collection('board');

    // Locate all the entries using find
    collection.find({board: 1}).toArray(function(err, results) {
        var board = results[0];
        board.cards = {};
        if (board.state < 3) {
            board.flop = [{"name": "1"}, {"name": "2"}, {"name": "3"}];
        }

        db.collection('gamer').find({board: 1}).toArray(function(err, results) {
            board.gamers = results;
            for (i in board.gamers) {
                if (board.state < 4) {
                    board.gamers[i].cards = [{"name": "1"}, {"name": "2"}, {"name": "3"}, , {"name": "4"}];
                    board.gamers[i].combination = {};
                    board.gamers[i].denied = [];
                    board.gamers[i].buy = [];
                    board.gamers[i].question = [];
                    board.gamers[i].rank = '';
                }
            }
            console.dir(JSON.stringify(board));
            db.close();
        });

    });
})