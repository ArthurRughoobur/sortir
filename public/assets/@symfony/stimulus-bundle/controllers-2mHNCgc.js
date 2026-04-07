import controller_0 from "../ux-live-component/live_controller.js";
import "../ux-live-component/live.min.css";
import controller_1 from "../../controllers/hello_controller.js";
export const eagerControllers = {"live": controller_0, "hello": controller_1};
export const lazyControllers = {"csrf-protection": () => import("../../controllers/csrf_protection_controller.js")};
export const isApplicationDebug = true;