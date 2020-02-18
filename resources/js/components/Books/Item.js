import React from 'react';
import { ListGroup } from 'react-bootstrap';

/**
 * display a particular author.
 * @param props.onClick what to do when an author is clicked.
 * @returns the author's details.
 */
export default function(props) {
    const { author, index, showID } = props;

    return (
        <ListGroup.Item
            data-step="10"
            data-intro= "Made a mistake? No problem. Click on their names to un-assign them from this book. Authors that do not appear on this list won't be assigned to your new book."
            action
            onClick={e => {
                props.onClick(author.ID);
            }}
        >
            {//for new authors, they do not have an actual ID, so don't display them:
            showID ? (
                <>
                    {' '}
                    {author.ID +
                        ', ' +
                        author.firstName +
                        ', ' +
                        author.lastName}{' '}
                </>
            ) : (
                <> {author.firstName + ', ' + author.lastName} </>
            )}
        </ListGroup.Item>
    );
}
