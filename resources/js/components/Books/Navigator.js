import React from 'react';
import {Button,ButtonGroup} from 'react-bootstrap';

/**
 * Handles navigation from 1 step to another.
 * @param props.step : the current UI step
 * @param props.min: initial step number
 * @param props.max : last step number
 * @param props.setStep: to increment/decrement  the UI step.
 */
export default function (props) {
    const {step,min,max,setStep} = props;

    /* initial step only has a forward button.
     * last step only has a backward button (submit button handled elsewhere).
     */
    return (
        <ButtonGroup>
        {step > min
         ? <Button variant="primary"onClick= {e => setStep(step-1)}> &lt; </Button>
         : null
        }
        {step < max
        ? <Button variant="primary"onClick= {e => setStep(step+1)}> &gt; </Button>
        : null
        }



        </ButtonGroup>

    );

}
