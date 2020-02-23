import React  from 'react';
import {Alert } from 'react-bootstrap';
export default function (props) {
    const {message} = props;
    return(
        <Alert variant='danger'>
            {message}
        </Alert>
    );
}
