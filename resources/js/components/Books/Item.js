import React from 'react';
import {ListGroup} from 'react-bootstrap';

export default function (props) {
    const {author,index,showID} = props;


    //display a particular author. onClick action: delete the author from the list.

    return (
        <ListGroup.Item action  onClick={e => {props.onClick(author.ID)}}>
    {//for new authors, they do not an actual ID, so don't display them:
        showID
        ? <> {author.ID + ', ' +  author.firstName  + ', '+ author.lastName} </>
        : <> {   author.firstName  + ', '+ author.lastName} </>

    }

    </ListGroup.Item>

    );
}
