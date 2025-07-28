<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Poker</title>
</head>
<body>
    <p>Poker Room 1</p>
    <div class="p_table">

    </div>
<style>
    .p_table{
        display: flex;
        flex-direction: row;
        flex-wrap: wrap;
        gap: 40px;
    }
</style>
<script>
    const room = 'room1'; // Could be dynamic
    const conn = new WebSocket('ws://localhost:8080/?room=' + room);

    conn.onopen = () => {
        console.log('Connected to room:', room);
    };

    conn.onmessage = e => {
        const data = JSON.parse(e.data);
        if (data.type == 'main_join') {
            $(".p_table").append(`  <div class="p_player" data-id="`+data.id+`">
                                        <span>Player #`+data.id+`</span>
                                        <div class="p_cards">
                                            <img style="border: 1px solid black" src="Assets/cards/$dealedCards[0].svg" width="80" height="120"> <img style="border: 1px solid black" src="Assets/cards/$dealedCards[1].svg" width="80" height="120">
                                        </div>
                                    </div>`);
            if(data.users_inside.length > 0){
                data.users_inside.forEach(function(val, i){
                    console.log(i)
                    if(val != data.id){
                        $(".p_table").append(`  <div class="p_player" data-id="`+val+`">
                                                    <span>Player #`+val+`</span>
                                                    <div class="p_cards">
                                                        <img style="border: 1px solid black" src="Assets/cards/$dealedCards[0].svg" width="80" height="120"> <img style="border: 1px solid black" src="Assets/cards/$dealedCards[1].svg" width="80" height="120">
                                                    </div>
                                                </div>`);
                    }
                })
            }
        } 
        else if(data.type == 'leave') {
            $(".p_player[data-id='"+data.id+"']").remove();
        }
        else {
            $(".p_table").append(`  <div class="p_player" data-id="`+data.id+`">
                                        <span>Player #`+data.id+`</span>
                                        <div class="p_cards">
                                            <img style="border: 1px solid black" src="Assets/cards/$dealedCards[0].svg" width="80" height="120"> <img style="border: 1px solid black" src="Assets/cards/$dealedCards[1].svg" width="80" height="120">
                                        </div>
                                    </div>`);
        }
    };

    function send(msg) {
        conn.send(msg);
    }
</script>
<script src="jquery.js"></script>
</body>
</html>