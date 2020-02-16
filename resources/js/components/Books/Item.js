import React from 'react';
import {ListGroup} from 'react-bootstrap';

export default function (props) {
    const {author,index} = props;
    //bug: cannot call onClick?
    //props for ListGroup: action  onclick={props.onClick(index)}>
    return (
        <ListGroup.Item>
        {author.ID + ', ' +  author.firstName  + ', '+ author.lastName}
    </ListGroup.Item>

    );
}
