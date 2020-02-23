import React  from 'react';
import {Alert } from 'react-bootstrap';
export default function (props) {
    const {message,variant} = props;
    return(
        <Alert variant={variant || 'danger'}>
            {message}
        </Alert>
    );
}
