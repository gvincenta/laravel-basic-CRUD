import React from 'react';
import { ListGroup, CardGroup, Card } from 'react-bootstrap';
import Item from './Item';

/**
 * Shows a list of existing authors and new authors that are assigned to the new book.
 * @param props.step: 2nd or 3rd step.
 * @param props.onNewAuthorRemove : how to unassign new author from the new book.
 * @param props.onExistingAuthorRemove :  how to unassign existing author from the new book.
 * @param props.newAuthors : where to store the new author's list.
 * @oaram props.existingAuthors: where to store the existing author's list.
 */
export default function(props) {
    const {
        step,
        onNewAuthorRemove,
        onExistingAuthorRemove,
        newAuthors,
        existingAuthors
    } = props;
    return (
        <CardGroup>
            {step === 3 ? ( // new authors' list rendered only in step 3:
                <Card>
                    <Card.Body>
                        <Card.Title>Assigned new authors: </Card.Title>
                    </Card.Body>
                    <ListGroup>
                        {loop(newAuthors, onNewAuthorRemove, false)}
                    </ListGroup>
                </Card>
            ) : null}
            <Card>
                <Card.Body>
                    <Card.Title>Assigned existing authors: </Card.Title>
                </Card.Body>
                <ListGroup>
                    {loop(existingAuthors, onExistingAuthorRemove, true)}
                </ListGroup>
            </Card>
        </CardGroup>
    );
}
function loop(x, onRemove, showID) {
    var array = [];
    for (var i = 0; i < x.length; i++) {
        array.push(
            React.createElement(Item, {
                author: x[i],
                key: i,
                index: i,
                onClick: onRemove,
                showID
            })
        );
    }
    return array;
}
