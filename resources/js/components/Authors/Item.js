import React, { useState, useEffect } from 'react';
import {Button } from 'react-bootstrap';

export default function Item(props) {
    const {author,onClick} = props;
    return (
        <tr>
            <td>{author.ID}</td>
            <td>{author.firstName}</td>
            <td>{author.lastName}</td>
            <td> <Button onClick={
    () => {
        onClick(author.ID,author.firstName,author.lastName);
    }
    }> Change Name </Button> </td>
        </tr>
    )
}
