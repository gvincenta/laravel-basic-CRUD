import React from 'react';
import {ListGroup,CardGroup,Card} from 'react-bootstrap';
import Item from './Item';

export default function (props) {
    const {step,onNewAuthorRemove,onExistingAuthorRemove,newAuthors,existingAuthors} = props;
    return (
        <CardGroup>
        {step===3
        ?
            <Card>
                <Card.Body>
                    <Card.Title>Assigned new authors: </Card.Title>
                </Card.Body>
                <ListGroup>
                    {loop(newAuthors,onNewAuthorRemove,false)}
                </ListGroup>
            </Card>
        : null
        }
            <Card>
                <Card.Body>
                    <Card.Title>Assigned existing authors: </Card.Title>
                </Card.Body>
                <ListGroup>
                    {loop(existingAuthors,onExistingAuthorRemove,true)}
                </ListGroup>
            </Card>
    </CardGroup>
    );

}
function loop(x,onRemove,showID) {

    var array = [];
    for (var i = 0 ; i < x.length; i++){
        array.push(  React.createElement(
            Item,
            {author: x[i], key:i, index: i, onClick:onRemove, showID}
        ))
    }
    return array;

}
